"""Mock lokal F-5. Semua respons memakai fixture fiktif, tanpa database."""
import argparse
import json
import re
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path
from urllib.parse import parse_qs, urlsplit

ROOT = Path(__file__).resolve().parent
ENDPOINT = "/api/payroll/v1/rekap-biaya"
UUID_PATTERN = re.compile(r"^[0-9a-fA-F]{8}-(?:[0-9a-fA-F]{4}-){3}[0-9a-fA-F]{12}$")
TOKEN_ROLES = {
    "mock-manajemen": "manajemen",
    "mock-keuangan": "petugas_keuangan",
    "mock-e2": "layanan_e2",
    "mock-karyawan": "karyawan",
    "mock-tenaga-ahli": "tenaga_ahli",
}
ALLOWED_ROLES = {"manajemen", "petugas_keuangan", "layanan_e2"}
FIXTURE_FILES = ["success.json", "success-empty.json", "success-termin-only.json"]


def load_example(name):
    return json.loads((ROOT / "examples" / name).read_text(encoding="utf-8"))


class Handler(BaseHTTPRequestHandler):
    def log_message(self, *_):
        # Header dan token tidak dicatat.
        pass

    def send_json(self, status, payload, extra_headers=None):
        body = json.dumps(payload, ensure_ascii=False, allow_nan=False).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.send_header("Cache-Control", "no-store")
        for name, value in (extra_headers or {}).items():
            self.send_header(name, value)
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        parsed = urlsplit(self.path)
        if parsed.path != ENDPOINT:
            self.send_json(404, {"code": "ROUTE_NOT_FOUND", "message": "Endpoint tidak ditemukan.", "details": []})
            return
        headers = self.headers.get_all("Authorization", [])
        parts = headers[0].split() if len(headers) == 1 else []
        role = TOKEN_ROLES.get(parts[1]) if len(parts) == 2 and parts[0].lower() == "bearer" else None
        if role is None:
            self.send_json(401, load_example("error-401.json"), {"WWW-Authenticate": "Bearer"})
            return
        if role not in ALLOWED_ROLES:
            self.send_json(403, load_example("error-403.json"))
            return
        values = parse_qs(parsed.query, keep_blank_values=True).get("periodeId", [])
        if len(values) != 1 or not UUID_PATTERN.fullmatch(values[0]):
            self.send_json(400, load_example("error-400.json"))
            return
        target = values[0].lower()
        for name in FIXTURE_FILES:
            payload = load_example(name)
            if payload["periodeId"] == target:
                self.send_json(200, payload)
                return
        self.send_json(404, load_example("error.json"))

    def reject_method(self):
        self.send_json(405, {"code": "METHOD_NOT_ALLOWED", "message": "Gunakan GET.", "details": []}, {"Allow": "GET"})

    do_POST = reject_method
    do_PUT = reject_method
    do_PATCH = reject_method
    do_DELETE = reject_method


def main():
    parser = argparse.ArgumentParser(description="Mock rekap biaya F-5 dengan data fiktif.")
    parser.add_argument("--port", type=int, default=8000)
    args = parser.parse_args()
    # Hanya di komputer lokal. Autentikasi F-1 belum diintegrasikan.
    server = ThreadingHTTPServer(("127.0.0.1", args.port), Handler)
    print(f"Mock F-5 siap di http://127.0.0.1:{server.server_port}", flush=True)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()


if __name__ == "__main__":
    main()
