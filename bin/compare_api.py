#!/usr/bin/env python3
"""
يقارن استجابات API الجديد بالقديم.

  python3 compare_api.py                       # كل الحالات
  python3 compare_api.py top_                  # الحالات التي يحتوي اسمها/رابطها على النص
  python3 compare_api.py -v                    # يعرض الفروق التفصيلية دائماً
  python3 compare_api.py --new http://localhost:9001/api.php --old http://localhost:9001/api/request.php

حالات إضافية: ملف نصي (سطر لكل حالة: استعلام  [# expect-diff])
  python3 compare_api.py --cases my_cases.txt

رمز الخروج: 0 = لا فروق غير متوقعة، 1 = يوجد فرق غير متوقع أو خطأ اتصال.
"""
import argparse, json, sys, urllib.request, urllib.error

NEW = "http://localhost:9001/api.php"
OLD = "http://localhost:9001/api/request.php"
IGNORE = {"time", "query", "source"}          # تتغير بين الطلبات
U = "Mr.Ibrahem"                              # غيّره لمستخدم موجود عندك

# (الاستعلام, يُتوقع اختلافه؟)  — الاختلاف المتوقع = إصلاح مقصود في الجديد
CASES = [
    # --- top_*
    ("get=top_langs", False),
    ("get=top_users", False),
    ("get=top_users&year=2024&month=3", False),
    ("get=top_langs&user_group=Wiki&cat=RTT", False),
    ("get=top_users&limit=5", False),
    (f"get=top_lang_of_users&users[]={U}&users[]=Ibrahem", False),
    ("get=top_lang_of_users", False),
    (f"get=top_lang_of_users&users[]={U}&limit=5", True),     # القديم: ; تكسر LIMIT
    # --- category / missing
    ("get=missing_by_lang_and_category&lang=ar&limit=5", False),
    ("get=missing&lang=ar&limit=5", False),
    ("get=exists_by_lang_and_category&lang=ar&cat=RTT&limit=5", False),
    ("get=exists_statics_by_category", False),
    ("get=exists_statics_by_category&limit=5", True),         # القديم: ; تكسر LIMIT
    ("get=statics_by_category&category=RTT", False),
    ("get=missing_by_lang_and_category", False),              # error: lang is missing
    # --- pages / meta
    ("get=pages&limit=1", False),
    ("get=pages&limit=10", False),
    (f"get=pages&user={U}&limit=10", False),
    ("get=pages&lang=ar&cat=RTT", True),                      # القديم: cat وحده لا يفلتر
    ("get=pages&select=lang&distinct=1", False),
    ("get=pages&order=pupdate&limit=5", False),
    ("get=pages&offset=5", True),                             # الجديد يتجاهل offset بلا limit
    ("get=publish_reports&limit=1", False),
    ("get=in_process&limit=1", False),
    ("get=in_process&group=lang&limit=10", False),
    # --- views / leaderboard / status
    ("get=views&limit=10", False),
    (f"get=user_views&user={U}&limit=10", False),
    ("get=user_views", True),                                 # الجديد: error صريح
    ("get=lang_views&lang=ar&limit=10", False),
    ("get=leaderboard_table&cat=RTT&limit=10", False),
    ("get=leaderboard_table_formated&limit=50", False),
    ("get=status&year=2024", False),
    ("get=status&year=2024&limit=5", True),                   # القديم: ; تكسر LIMIT
    # --- أخرى
    ("get=langs", False),
    ("get=users&userlike=Mr", False),
    ("get=category_members&cat=RTT", False),
    ("get=coordinators&limit=5", False),
    ("get=qids&limit=10", False),
    ("get=qids&dis=empty&limit=10", False),
    ("get=count_pages&limit=10", False),
    ("get=words&limit=10", False),
    ("get=titles&limit=5", False),
    ("get=revids&limit=5", False),
    ("get=pages_with_views&limit=10", False),
    ("get=pages_by_user_or_lang&lang=ar&year=2024", True),    # الجديد يتعامل مع year وحدها
    ("get=pages_langs", False),
    ("get=users_by_last_pupdate", False),
    ("get=nonexistent", False),
]


def fetch(url):
    try:
        with urllib.request.urlopen(url, timeout=60) as r:
            body = r.read().decode("utf-8", "replace")
            status = r.status
    except urllib.error.HTTPError as e:
        body, status = e.read().decode("utf-8", "replace"), e.code
    except Exception as e:
        return None, f"connection error: {e}"
    try:
        return json.loads(body), status
    except ValueError:
        return None, f"not JSON (HTTP {status}): {body[:120]!r}"


def clean(d, ignore):
    return {k: v for k, v in d.items() if k not in ignore} if isinstance(d, dict) else d


def diff(a, b, path="", out=None, limit=8):
    """a = قديم، b = جديد"""
    out = [] if out is None else out
    if len(out) >= limit:
        return out
    if type(a) != type(b):
        out.append((path or "$", a, b))
    elif isinstance(a, dict):
        for k in sorted(set(a) | set(b)):
            if k not in a:
                out.append((f"{path}.{k}", "<غائب>", b[k]))
            elif k not in b:
                out.append((f"{path}.{k}", a[k], "<غائب>"))
            else:
                diff(a[k], b[k], f"{path}.{k}", out, limit)
    elif isinstance(a, list):
        if len(a) != len(b):
            out.append((f"{path}[len]", len(a), len(b)))
        for i, (x, y) in enumerate(zip(a, b)):
            diff(x, y, f"{path}[{i}]", out, limit)
            if len(out) >= limit:
                break
    elif a != b:
        out.append((path, a, b))
    return out[:limit]


def short(v, n=70):
    s = json.dumps(v, ensure_ascii=False) if not isinstance(v, str) else v
    return s if len(s) <= n else s[: n - 1] + "…"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("filter", nargs="?", default="")
    ap.add_argument("--new", default=NEW)
    ap.add_argument("--old", default=OLD)
    ap.add_argument("--cases", help="ملف حالات إضافية")
    ap.add_argument("--ignore", default="", help="حقول إضافية للتجاهل (مفصولة بفاصلة)")
    ap.add_argument("-v", "--verbose", action="store_true")
    args = ap.parse_args()

    ignore = IGNORE | {x for x in args.ignore.split(",") if x}
    cases = list(CASES)
    if args.cases:
        for line in open(args.cases, encoding="utf-8"):
            line = line.strip()
            if line and not line.startswith("#"):
                cases.append((line.split("#")[0].strip().lstrip("?"), "expect-diff" in line))
    cases = [c for c in cases if args.filter in c[0]]

    bad = same = expected = errors = 0
    for q, expect in cases:
        new, ns = fetch(f"{args.new}?{q}")
        old, os_ = fetch(f"{args.old}?{q}")
        if new is None or old is None:
            errors += 1
            print(f"✗ ERR   {q}\n        جديد: {ns if new is None else 'ok'} | قديم: {os_ if old is None else 'ok'}")
            continue
        d = diff(clean(old, ignore), clean(new, ignore))
        ln = f"len {old.get('length')}→{new.get('length')}" if isinstance(old, dict) else ""
        if not d:
            same += 1
            print(f"✓ same  {q}   [{ln}]" + ("   ⚠ كان متوقعاً أن يختلف" if expect else ""))
        elif expect:
            expected += 1
            print(f"≈ DIFF* {q}   [{ln}]   (فرق متوقع)")
            if args.verbose:
                for p, a, b in d: print(f"        {p}: {short(a)}  →  {short(b)}")
        else:
            bad += 1
            print(f"✗ DIFF  {q}   [{ln}]")
            for p, a, b in d: print(f"        {p}: قديم={short(a)}  جديد={short(b)}")

    print(f"\nمتطابق {same} | فرق متوقع {expected} | فرق غير متوقع {bad} | أخطاء اتصال {errors}")
    sys.exit(1 if bad or errors else 0)


if __name__ == "__main__":
    main()
