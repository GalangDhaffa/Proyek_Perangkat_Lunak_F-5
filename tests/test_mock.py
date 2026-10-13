"""Uji HTTP nyata untuk perilaku kontrak dan pembatasan data."""
import json
import os
import subprocess
import sys
import unittest
from decimal import Decimal
from pathlib import Path
from urllib.error import HTTPError
from urllib.request import Request, build_opener, ProxyHandler

ROOT = Path(__file__).resolve().parents[1]
PATH = "/api/payroll/v1/rekap-biaya"
FULL = "01a1193d-2500-7c04-854b-8bf03c711dde"
EMPTY = "01a1193d-2500-7c05-854b-8bf03c711dde"
TERMIN = "01a1193d-2500-7c06-854b-8bf03c711dde"
UNKNOWN = "01a1193d-2500-7c07-854b-8bf03c711dde"


class MockContractTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.process = subprocess.Popen(
            [sys.executable, str(ROOT / "mock_server.py"), "--port", "0"],
            stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True,
        )
        ready = cls.process.stdout.readline().strip()
        if "http://127.0.0.1:" not in ready:
            cls.process.terminate()
            raise RuntimeError("Mock tidak berhasil dijalankan: " + ready)
        cls.base = ready.split()[-1]
        cls.opener = build_opener(ProxyHandler({}))

    @classmethod
    def tearDownClass(cls):
        cls.process.terminate()
        cls.process.communicate(timeout=5)

    def call(self, query="", token="mock-e2", method="GET", path=PATH):
        headers = {} if token is None else {"Authorization": "Bearer " + token}
        request = Request(self.base + path + query, headers=headers, method=method)
        try:
            response = self.opener.open(request, timeout=3)
        except HTTPError as error:
            response = error
        with response:
            data = json.loads(response.read())
            return response.status, data, response.headers

    def test_success_and_no_personal_fields(self):
        status, data, _ = self.call("?periodeId=" + FULL)
        self.assertEqual(status, 200)
        fixture = json.loads((ROOT / "examples/success.json").read_text())
        self.assertEqual(data, fixture)
        spec = json.loads((ROOT / "openapi.json").read_text())
        fields = set(spec["components"]["schemas"]["RekapBiaya"]["required"])
        self.assertEqual(set(data), fields)
        self.assertFalse(set(data) & {"employeeId", "nama", "rekening", "npwp", "gajiPokok", "slipId"})
        self.assertIsNone(data["totalBiaya"])
        self.assertTrue(data["adaData"])
        amount = lambda key: Decimal(str(data[key]))
        self.assertEqual(amount("totalGajiBersih"), amount("totalPendapatan") - amount("totalPotongan") - amount("totalBpjsKaryawan"))
        self.assertEqual(amount("totalPendapatan"), Decimal("6500000") + Decimal("500000") + amount("totalBonus"))

    def test_allowed_roles(self):
        for token in ["mock-manajemen", "mock-keuangan", "mock-e2"]:
            with self.subTest(token=token):
                self.assertEqual(self.call("?periodeId=" + FULL, token)[0], 200)

    def test_forbidden_roles(self):
        for token in ["mock-karyawan", "mock-tenaga-ahli"]:
            with self.subTest(token=token):
                status, body, _ = self.call("?periodeId=" + FULL, token)
                self.assertEqual((status, body["code"]), (403, "FORBIDDEN"))

    def test_missing_and_invalid_auth(self):
        for token in [None, "", "bukan-token-demo"]:
            with self.subTest(token=token):
                status, body, headers = self.call("?periodeId=" + FULL, token)
                self.assertEqual((status, body["code"]), (401, "UNAUTHORIZED"))
                self.assertEqual(headers["WWW-Authenticate"], "Bearer")

    def test_missing_empty_malformed_and_duplicate_id(self):
        for query in ["", "?periodeId=", "?periodeId=salah", "?periodeId=" + FULL.replace("-", ""), "?periodeId=" + FULL + "&periodeId=" + EMPTY]:
            with self.subTest(query=query):
                status, body, _ = self.call(query)
                self.assertEqual((status, body["code"]), (400, "INVALID_PERIODE_ID"))

    def test_unknown_valid_uuid(self):
        status, body, _ = self.call("?periodeId=" + UNKNOWN)
        self.assertEqual((status, body["code"]), (404, "PERIODE_NOT_FOUND"))

    def test_empty_period_is_success(self):
        status, body, _ = self.call("?periodeId=" + EMPTY)
        self.assertEqual(status, 200)
        self.assertFalse(body["adaData"])
        self.assertIsNone(body["totalBiaya"])
        for key in ["jumlahKaryawan", "jumlahTermin", "totalPendapatan", "totalBonus", "totalPotongan", "totalBpjsKaryawan", "totalGajiBersih", "totalBpjsPerusahaan", "totalTermin"]:
            self.assertEqual(body[key], 0, key)

    def test_termin_only_period(self):
        status, body, _ = self.call("?periodeId=" + TERMIN)
        self.assertEqual(status, 200)
        self.assertTrue(body["adaData"])
        self.assertEqual(body["totalTermin"], 25000000)
        self.assertEqual(body["jumlahTermin"], 1)
        for key in ["jumlahKaryawan", "totalPendapatan", "totalBonus", "totalPotongan", "totalBpjsKaryawan", "totalGajiBersih", "totalBpjsPerusahaan"]:
            self.assertEqual(body[key], 0, key)

    def test_uppercase_id_and_repeat_read(self):
        first = self.call("?periodeId=" + FULL)[1]
        second = self.call("?periodeId=" + FULL.upper())[1]
        self.assertEqual(first, second)

    def test_unsupported_method(self):
        status, body, headers = self.call("?periodeId=" + FULL, method="POST")
        self.assertEqual((status, body["code"]), (405, "METHOD_NOT_ALLOWED"))
        self.assertEqual(headers["Allow"], "GET")


if __name__ == "__main__":
    unittest.main(verbosity=2)
