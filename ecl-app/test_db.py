# test_db.py — ডাটাবেজ কানেকশন পরীক্ষা (Test DB connection & table creation)

from db import engine, init_db
from sqlalchemy import inspect

def test_connection():
    """MySQL-এ কানেক্ট করে টেবিল বানাও এবং নামগুলো দেখাও।"""
    print("[*] Connecting to MySQL...")

    # টেবিল তৈরি করো (Create tables)
    init_db()

    # ডাটাবেজে কী কী টেবিল আছে দেখো (List all tables)
    inspector = inspect(engine)
    tables = inspector.get_table_names()

    print(f"\n[OK] Found {len(tables)} table(s) in database:")
    for t in tables:
        print(f"   - {t}")

    print("\n[SUCCESS] Connection & table creation successful!")


if __name__ == "__main__":
    test_connection()
