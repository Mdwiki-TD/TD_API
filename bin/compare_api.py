#!/usr/bin/env python3
"""
يقارن استجابات API الجديد بالقديم.

  python3 bin/compare_api.py                       # كل الحالات
  python3 bin/compare_api.py top_                  # الحالات التي يحتوي اسمها/رابطها على النص
  python3 bin/compare_api.py -v                    # يعرض الفروق التفصيلية دائماً
  python3 bin/compare_api.py --new http://localhost:9001/api.php --old http://localhost:9001/api/request.php

حالات إضافية: ملف نصي (سطر لكل حالة: استعلام  [# expect-diff])
  python3 bin/compare_api.py --cases my_cases.txt

رمز الخروج: 0 = لا فروق غير متوقعة، 1 = يوجد فرق غير متوقع أو خطأ اتصال.
"""
import argparse
import json
import sys
import urllib.request
import urllib.error

NEW = "http://localhost:9001/api.php"
OLD = "http://localhost:9001/api/request.php"
IGNORE = {"time", "query", "source"}          # تتغير بين الطلبات

U = "Mr.Ibrahem"                              # غيّره لمستخدم موجود عندك

# (الاستعلام, يُتوقع اختلافه؟)  — الاختلاف المتوقع = إصلاح مقصود في الجديد
CASES = [
    # --- category_members / categories
    ("get=category_members", False),
    ("get=category_members&cat=RTT", False),
    ("get=exists_by_lang_and_category&lang=ar&cat=RTT&limit=5", False),
    ("get=exists_statics_by_category", False),
    ("get=exists_statics_by_category&limit=5", True),         # القديم: كان يفشل بسبب ;
    ("get=statics_by_category&category=RTT&limit=5", False),

    # --- coordinators / users
    ("get=coordinators&limit=5", False),
    ("get=coordinators&order=username", False),                # يجب ألا يُحدث خطأ SQL
    ("get=users", False),
    ("get=users&userlike=false", False),
    ("get=users&userlike=Mr", False),
    ("get=users&userlike=O'Brien", False),
    ("get=users_by_last_pupdate", False),
    ("get=users_by_last_pupdate&limit=5", True),               # يُتوقع أن يختلف (كان يفشل بسبب ;)
    ("get=user_access&limit=3", False),
    ("get=user_lang_status&select=lang&user=Mr.Ibrahem", False),
    ("get=user_status&select=year&distinct=1", False),
    ("get=user_status&user=Mr.Ibrahem", False),

    # --- count_pages / graph_data / in_process
    ("get=count_pages&limit=10", False),
    ("get=count_pages&order=count", False),                    # يعمل إن عُرّف order في JSON
    ("get=count_pages&user=Mr.Ibrahem", False),
    ("get=graph_data", False),
    ("get=graph_data&order=c", False),
    ("get=in_process&group=lang", False),
    ("get=in_process&group=lang&limit=10", False),
    ("get=in_process&group=lang&order=lang", False),
    ("get=in_process&lang=ar&cat=RTT", False),
    ("get=in_process&limit=10", False),
    ("get=in_process&order=add_date&limit=10", False),
    ("get=in_process&user=Mr.Ibrahem", False),

    # --- views / lang_views / user_views
    ("get=lang_views", True),                                 # error: "lang param required"
    ("get=lang_views&lang=ar&limit=10", False),
    ("get=lang_views2&lang=ar", False),
    ("get=user_views", True),                                 # error: "user param required"
    ("get=user_views&user=false", True),                      # نفس الخطأ
    ("get=user_views&user=Mr.Ibrahem&limit=10", False),
    ("get=user_views2&user=Mr.Ibrahem", False),
    ("get=views&limit=10", False),
    ("get=views&order=views", False),                         # ترتيب views أو التأكد من سلوك add_order

    # --- langs / language_settings
    ("get=langs", False),                                     # redirects مصفوفة / charset / langs_format
    ("get=language_settings", False),

    # --- leaderboard_table
    ("get=leaderboard_table&cat=RTT&limit=10", False),
    ("get=leaderboard_table&order=lang", False),
    ("get=leaderboard_table_formated&limit=10", False),
    ("get=leaderboard_table_formated&limit=50", False),

    # --- missing / missing_by_lang_and_category
    ("get=missing&lang=ar&limit=5", False),                   # يمر عبر subs
    ("get=missing&lang=ar&order=en_views&limit=5", False),    # order مدعوم هنا فقط
    ("get=missing_by_lang_and_category", True),               # error: lang is missing
    ("get=missing_by_lang_and_category&lang=ar&limit=5", False),
    ("get=missing_by_lang_and_category&lang=ar&order=x", False), # يُتجاهل
    ("get=missing_by_lang_and_category&lang=zz", False),      # لغة غير صالحة: نتيجة فارغة

    # --- pages / pages_by_user_or_lang / pages_*
    ("get=pages&campaign=Main", False),
    ("get=pages&campaign=Main&limit=10", False),
    ("get=pages&cat=RTT", True),                              # كان يرجع كل الصفوف، يفلتر الآن
    ("get=pages&lang=ar&cat=RTT", False),
    ("get=pages&limit=10", False),
    ("get=pages&limit=3", False),                             # مع Cookie: test=1
    ("get=pages&limit=3&apcu", False),                        # الكاش يعمل (QueryExecutor الجديد)
    ("get=pages&limit=3&test", False),                        # لا طباعة إضافية، JSON سليم
    ("get=pages&limit=5", False),
    ("get=pages&limit=5&apcu", False),                        # source: apcu / source: db
    ("get=pages&limit=5&apcu=false", False),
    ("get=pages&limit=5&offset=5", False),
    ("get=pages&offset=5", True),                             # offset يُتجاهل بلا limit
    ("get=pages&order=pupdate&limit=5", False),
    ("get=pages&select=count&count=*", False),
    ("get=pages&select=lang&distinct=1", False),
    ("get=pages&test", False),
    ("get=pages&title=Aspirin", False),
    ("get=pages&title=Friedreich%27s%20ataxia", False),
    ("get=pages&title=Friedreich's ataxia", False),
    ("get=pages&title[]=A&title[]=B", False),                 # إن كان title من نوع array في JSON
    ("get=pages&user=Mr.Ibrahem&limit=10", False),
    ("get=pages_by_user_or_lang&group=lang", False),
    ("get=pages_by_user_or_lang&lang=ar&year=2024", False),
    ("get=pages_by_user_or_lang&user=Mr.Ibrahem&limit=10", False),
    ("get=pages_by_user_or_lang&year=2024", True),           # كان يفشل، يعمل الآن
    ("get=pages_langs", False),
    ("get=pages_users&limit=10", False),
    ("get=pages_users&user=Mr.Ibrahem", False),
    ("get=pages_users_langs", False),
    ("get=pages_users_to_main&lang=ar", False),
    ("get=pages_users_to_main&limit=10", False),
    ("get=pages_with_views&group=lang", False),
    ("get=pages_with_views&lang=ar&cat=RTT&limit=10", False),
    ("get=pages_with_views&limit=10", False),
    ("get=pages_with_views&order=pupdate&limit=10", False),
    ("get=pages_with_views&title=Crohn's disease", False),
    ("get=pages_with_views&user=Mr.Ibrahem&limit=10", False),

    # --- publish_reports / publish_reports_stats
    ("get=publish_reports&distinct=1&select=lang", False),
    ("get=publish_reports&lang=ar&limit=5", False),
    ("get=publish_reports&limit=10", False),
    ("get=publish_reports&order=year&order_direction=asc&limit=5", False),
    ("get=publish_reports&select=lang&distinct=1", False),
    ("get=publish_reports&year=2024&limit=5", False),
    ("get=publish_reports&year=2024&month=3&lang=ar", False),
    ("get=publish_reports_stats&lang=ar", True),             # يعمل الآن
    ("get=publish_reports_stats&limit=10", False),

    # --- qids
    ("get=qids&dis=duplicate&limit=10", False),
    ("get=qids&dis=empty&limit=10", False),
    ("get=qids&dis=غير_موجود", False),                       # يجب أن يرجع all
    ("get=qids&limit=10", False),
    ("get=qids_others&dis=all&limit=10", False),

    # --- status / settings / top_users / others
    ("get=revids&limit=5", False),
    ("get=settings&apcu", False),                             # source: db دائماً
    ("get=status&cat=RTT&user_group=Wiki", False),
    ("get=status&year=2024", False),
    ("get=status&year=2024&limit=5", True),                   # يعمل الآن (كان ; يكسره)
    ("get=titles&limit=5", False),
    ("get=top_users&limit=5", False),
    ("get=views_new&limit=10", False),
    ("get=words&limit=10", False),
    ("get=nonexistent", False),                               # invalid get request
]


def fetch(url):
    print(f"fetching {url}…")

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
