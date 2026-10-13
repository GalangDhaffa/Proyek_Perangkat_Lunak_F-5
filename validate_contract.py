"""Validasi OpenAPI dan semua fixture. Install requirements-dev.txt dahulu."""
import json
from pathlib import Path
from decimal import Decimal

import yaml
from openapi_spec_validator import validate
from openapi_schema_validator import OAS30Validator, oas30_format_checker

ROOT = Path(__file__).resolve().parent


def main():
    spec = yaml.safe_load((ROOT / "openapi.yaml").read_text(encoding="utf-8"))
    duplicate = json.loads((ROOT / "openapi.json").read_text(encoding="utf-8"))
    assert spec == duplicate, "openapi.yaml dan openapi.json berbeda"
    validate(spec)
    operation = spec["paths"]["/api/payroll/v1/rekap-biaya"]["get"]
    assert operation["operationId"] == "getRekapBiaya"
    for status, response in operation["responses"].items():
        media = response["content"]["application/json"]
        for example in media["examples"].values():
            filename = example["x-file"]
            # Decimal menjaga pemeriksaan multipleOf 0.01 tetap eksak.
            payload = json.loads((ROOT / filename).read_text(), parse_float=Decimal)
            inline = json.loads(json.dumps(example["value"]), parse_float=Decimal)
            assert payload == inline, filename + " berbeda dari contoh pada kontrak"
            root_schema = json.loads(json.dumps({**media["schema"], "components": spec["components"]}), parse_float=Decimal)
            validator = OAS30Validator(root_schema, format_checker=oas30_format_checker)
            validator.validate(payload)
            if status == "200":
                assert payload["totalBiaya"] is None
                assert "employeeId" not in payload and "nama" not in payload
            print("LULUS", status, filename)
    print("LULUS OpenAPI 3.0.3, referensi skema, format, YAML/JSON, dan contoh respons")


if __name__ == "__main__":
    main()
