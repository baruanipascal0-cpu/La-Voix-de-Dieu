from __future__ import annotations

import gzip
import json
import re
import struct
from collections import Counter
from pathlib import Path


ROOT = Path(__file__).resolve().parent
OUT = ROOT / "static_scan"


ASCII_RE = re.compile(rb"[\x20-\x7e]{4,}")
UTF16LE_RE = re.compile(rb"(?:[\x20-\x7e]\x00){4,}")
URL_RE = re.compile(r"https?://[^\s\"'<>),\\]+", re.IGNORECASE)
DOMAIN_RE = re.compile(
    r"\b[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)+"
    r"(?::\d+)?(?:/[^\s\"'<>),\\]*)?",
    re.IGNORECASE,
)
ENDPOINT_RE = re.compile(
    r"(?<![a-zA-Z0-9])/(?:api|auth|login|register|users?|categories?|posts?|messages?|rooms?|calls?|"
    r"broadcasts?|sermons?|videos?|audios?|radios?|events?|notifications?|ministries?|churches?|profiles?)"
    r"[a-zA-Z0-9_./{}:-]*",
    re.IGNORECASE,
)

KEYWORDS = [
    "baruti",
    "tabernacle",
    "la voix",
    "dieu",
    "kindu",
    "eglise",
    "eglises",
    "église",
    "church",
    "pasteur",
    "pastor",
    "predication",
    "prédication",
    "sermon",
    "message",
    "audio",
    "video",
    "radio",
    "direct",
    "live",
    "podcast",
    "priere",
    "prière",
    "pray",
    "prayer",
    "chat",
    "forum",
    "appel",
    "call",
    "groupe",
    "group",
    "notification",
    "user",
    "login",
    "register",
    "profile",
    "favori",
    "favorite",
    "category",
    "categorie",
    "catégorie",
    "event",
    "evenement",
    "événement",
    "don",
    "donation",
    "offrande",
    "contact",
    "phone",
    "email",
]


def u16(data: bytes, offset: int) -> int:
    return struct.unpack_from("<H", data, offset)[0]


def u32(data: bytes, offset: int) -> int:
    return struct.unpack_from("<I", data, offset)[0]


def read_uleb128(data: bytes, offset: int) -> tuple[int, int]:
    result = 0
    shift = 0
    current = offset
    while current < len(data):
        value = data[current]
        current += 1
        result |= (value & 0x7F) << shift
        if value & 0x80 == 0:
            break
        shift += 7
    return result, current


def extract_printable_strings(data: bytes, min_len: int = 4) -> list[str]:
    values: set[str] = set()

    for match in ASCII_RE.finditer(data):
        raw = match.group(0)
        if len(raw) >= min_len:
            values.add(raw.decode("utf-8", errors="replace"))

    for match in UTF16LE_RE.finditer(data):
        raw = match.group(0)
        if len(raw) >= min_len * 2:
            values.add(raw.decode("utf-16le", errors="replace"))

    return sorted(values, key=lambda value: (value.lower(), value))


def parse_dex_strings(path: Path) -> list[str]:
    data = path.read_bytes()
    if not data.startswith(b"dex\n"):
        return []

    string_ids_size = u32(data, 0x38)
    string_ids_off = u32(data, 0x3C)
    strings: list[str] = []

    for index in range(string_ids_size):
        item_off = u32(data, string_ids_off + index * 4)
        _, cursor = read_uleb128(data, item_off)
        end = data.find(b"\x00", cursor)
        if end == -1:
            continue
        raw = data[cursor:end]
        strings.append(raw.decode("utf-8", errors="replace"))

    return strings


class BinaryXmlParser:
    RES_STRING_POOL_TYPE = 0x0001
    RES_XML_TYPE = 0x0003
    RES_XML_RESOURCE_MAP_TYPE = 0x0180
    RES_XML_START_NAMESPACE_TYPE = 0x0100
    RES_XML_END_NAMESPACE_TYPE = 0x0101
    RES_XML_START_ELEMENT_TYPE = 0x0102
    RES_XML_END_ELEMENT_TYPE = 0x0103
    UTF8_FLAG = 0x00000100

    def __init__(self, data: bytes):
        self.data = data
        self.strings: list[str] = []
        self.resources: list[int] = []
        self.lines: list[str] = []

    def parse(self) -> list[str]:
        if len(self.data) < 8 or u16(self.data, 0) != self.RES_XML_TYPE:
            return []

        offset = 8
        depth = 0
        while offset + 8 <= len(self.data):
            chunk_type = u16(self.data, offset)
            header_size = u16(self.data, offset + 2)
            chunk_size = u32(self.data, offset + 4)
            if chunk_size <= 0:
                break

            if chunk_type == self.RES_STRING_POOL_TYPE:
                self.strings = self._parse_string_pool(offset)
            elif chunk_type == self.RES_XML_RESOURCE_MAP_TYPE:
                self.resources = [
                    u32(self.data, pos)
                    for pos in range(offset + header_size, offset + chunk_size, 4)
                    if pos + 4 <= len(self.data)
                ]
            elif chunk_type == self.RES_XML_START_ELEMENT_TYPE:
                name_index = u32(self.data, offset + 20)
                name = self._string(name_index)
                attr_start = u16(self.data, offset + 24)
                attr_size = u16(self.data, offset + 26)
                attr_count = u16(self.data, offset + 28)
                attrs = []
                attr_base = offset + attr_start
                for attr_index in range(attr_count):
                    attrs.append(self._parse_attribute(attr_base + attr_index * attr_size))
                prefix = "  " * depth
                attr_text = "".join(f" {key}={json.dumps(value, ensure_ascii=False)}" for key, value in attrs)
                self.lines.append(f"{prefix}<{name}{attr_text}>")
                depth += 1
            elif chunk_type == self.RES_XML_END_ELEMENT_TYPE:
                depth = max(depth - 1, 0)
                name_index = u32(self.data, offset + 20)
                name = self._string(name_index)
                self.lines.append(f"{'  ' * depth}</{name}>")

            offset += chunk_size

        return self.lines

    def _parse_string_pool(self, offset: int) -> list[str]:
        header_size = u16(self.data, offset + 2)
        string_count = u32(self.data, offset + 8)
        flags = u32(self.data, offset + 20)
        strings_start = u32(self.data, offset + 28)
        utf8 = bool(flags & self.UTF8_FLAG)
        offsets = [u32(self.data, offset + header_size + index * 4) for index in range(string_count)]
        base = offset + strings_start
        return [self._read_pool_string(base + string_offset, utf8) for string_offset in offsets]

    def _read_pool_string(self, offset: int, utf8: bool) -> str:
        if utf8:
            _, cursor = self._read_length8(offset)
            byte_length, cursor = self._read_length8(cursor)
            raw = self.data[cursor : cursor + byte_length]
            return raw.decode("utf-8", errors="replace")

        char_length, cursor = self._read_length16(offset)
        raw = self.data[cursor : cursor + char_length * 2]
        return raw.decode("utf-16le", errors="replace")

    def _read_length8(self, offset: int) -> tuple[int, int]:
        first = self.data[offset]
        if first & 0x80:
            return ((first & 0x7F) << 8) | self.data[offset + 1], offset + 2
        return first, offset + 1

    def _read_length16(self, offset: int) -> tuple[int, int]:
        first = u16(self.data, offset)
        if first & 0x8000:
            return ((first & 0x7FFF) << 16) | u16(self.data, offset + 2), offset + 4
        return first, offset + 2

    def _string(self, index: int) -> str:
        if index == 0xFFFFFFFF:
            return ""
        if 0 <= index < len(self.strings):
            return self.strings[index]
        return f"@string_index_{index}"

    def _parse_attribute(self, offset: int) -> tuple[str, str]:
        name_index = u32(self.data, offset + 4)
        raw_value_index = u32(self.data, offset + 8)
        data_type = self.data[offset + 15]
        value_data = u32(self.data, offset + 16)

        name = self._string(name_index)
        value = self._format_typed_value(raw_value_index, data_type, value_data)
        return name, value

    def _format_typed_value(self, raw_value_index: int, data_type: int, value_data: int) -> str:
        if raw_value_index != 0xFFFFFFFF:
            return self._string(raw_value_index)
        if data_type == 0x03:
            return self._string(value_data)
        if data_type == 0x12:
            return "true" if value_data else "false"
        if data_type in (0x01, 0x02):
            return f"@0x{value_data:08x}"
        if data_type == 0x10:
            return str(value_data)
        if data_type == 0x11:
            return f"0x{value_data:08x}"
        return f"type=0x{data_type:02x}:0x{value_data:08x}"


def decode_binary_xml(path: Path) -> list[str]:
    try:
        return BinaryXmlParser(path.read_bytes()).parse()
    except Exception as exc:  # noqa: BLE001 - best-effort static analysis helper
        return [f"Could not parse {path}: {exc}"]


def collect_strings() -> dict[str, list[str]]:
    sources: dict[str, list[str]] = {}

    for dex_path in sorted(ROOT.glob("classes*.dex")):
        sources[str(dex_path.relative_to(ROOT))] = parse_dex_strings(dex_path)

    libapp = ROOT / "lib" / "arm64-v8a" / "libapp.so"
    if libapp.exists():
        sources[str(libapp.relative_to(ROOT))] = extract_printable_strings(libapp.read_bytes())

    arsc = ROOT / "resources.arsc"
    if arsc.exists():
        sources[str(arsc.relative_to(ROOT))] = extract_printable_strings(arsc.read_bytes())

    notices = ROOT / "assets" / "flutter_assets" / "NOTICES.Z"
    if notices.exists():
        try:
            sources[str(notices.relative_to(ROOT))] = extract_printable_strings(gzip.decompress(notices.read_bytes()))
        except OSError:
            sources[str(notices.relative_to(ROOT))] = extract_printable_strings(notices.read_bytes())

    for text_path in sorted((ROOT / "assets" / "flutter_assets").rglob("*")):
        if text_path.is_file() and text_path.suffix.lower() in {".json", ".md", ".svg", ".txt", ".xml"}:
            sources[str(text_path.relative_to(ROOT))] = extract_printable_strings(text_path.read_bytes())

    return sources


def interesting_values(sources: dict[str, list[str]]) -> list[tuple[str, str]]:
    values: list[tuple[str, str]] = []
    keyword_re = re.compile("|".join(re.escape(keyword) for keyword in KEYWORDS), re.IGNORECASE)

    for source, strings in sources.items():
        for value in strings:
            compact = " ".join(value.split())
            if not compact or len(compact) > 260:
                continue
            if URL_RE.search(compact) or ENDPOINT_RE.search(compact) or keyword_re.search(compact):
                values.append((source, compact))

    seen = set()
    unique = []
    for item in values:
        if item in seen:
            continue
        seen.add(item)
        unique.append(item)
    return sorted(unique, key=lambda item: (item[1].lower(), item[0]))


def write_lines(path: Path, lines: list[str]) -> None:
    path.write_text("\n".join(lines) + "\n", encoding="utf-8")


def main() -> None:
    OUT.mkdir(exist_ok=True)

    manifest_lines = decode_binary_xml(ROOT / "AndroidManifest.xml")
    write_lines(OUT / "AndroidManifest.decoded.txt", manifest_lines)

    sources = collect_strings()
    for source, strings in sources.items():
        safe_name = source.replace("\\", "__").replace("/", "__").replace(":", "_")
        write_lines(OUT / f"{safe_name}.strings.txt", strings)

    interesting = interesting_values(sources)
    write_lines(OUT / "interesting_strings.txt", [f"{source}: {value}" for source, value in interesting])

    all_text = "\n".join(value for strings in sources.values() for value in strings)
    urls = sorted(set(URL_RE.findall(all_text)))
    domains = sorted(
        domain
        for domain in set(DOMAIN_RE.findall(all_text))
        if "." in domain and not domain.lower().endswith((".class", ".xml", ".png", ".jpg", ".wav", ".so"))
    )
    endpoints = sorted(set(ENDPOINT_RE.findall(all_text)))

    write_lines(OUT / "urls.txt", urls)
    write_lines(OUT / "domains.txt", domains)
    write_lines(OUT / "endpoints.txt", endpoints)

    asset_manifest = ROOT / "assets" / "flutter_assets" / "AssetManifest.json"
    assets = []
    if asset_manifest.exists():
        assets = sorted(json.loads(asset_manifest.read_text(encoding="utf-8")).keys())

    library_counts = Counter()
    for value in sources.get("classes.dex", []):
        if value.startswith("L") and "/" in value:
            package = value.strip("L;").split("/")
            if len(package) >= 2:
                library_counts["/".join(package[:3])] += 1

    report = [
        "# APK Static Scan",
        "",
        "## Android Manifest",
        "",
        *manifest_lines[:120],
        "",
        "## Flutter Assets",
        "",
        *[f"- {asset}" for asset in assets],
        "",
        "## URLs",
        "",
        *[f"- {url}" for url in urls[:200]],
        "",
        "## Domains",
        "",
        *[f"- {domain}" for domain in domains[:200]],
        "",
        "## Endpoints",
        "",
        *[f"- {endpoint}" for endpoint in endpoints[:200]],
        "",
        "## Interesting Strings",
        "",
        *[f"- `{source}`: {value}" for source, value in interesting[:400]],
        "",
        "## Top DEX Packages",
        "",
        *[f"- {package}: {count}" for package, count in library_counts.most_common(80)],
    ]
    write_lines(OUT / "report.md", report)

    print(f"Wrote static scan to {OUT}")
    print(f"Manifest lines: {len(manifest_lines)}")
    print(f"Sources scanned: {len(sources)}")
    print(f"Interesting strings: {len(interesting)}")
    print(f"URLs: {len(urls)} Domains: {len(domains)} Endpoints: {len(endpoints)}")


if __name__ == "__main__":
    main()
