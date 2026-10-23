# db.py — ডাটাবেজ কানেকশন ও টেবিল তৈরি (Database connection & table setup)
# SQLAlchemy engine, .env থেকে credentials পড়ে (reads credentials from .env)

import os
from dotenv import load_dotenv
from sqlalchemy import (
    create_engine, MetaData, Table, Column,
    Integer, Float, String, DateTime, ForeignKey, inspect,
)
from sqlalchemy.sql import func

# .env ফাইল থেকে credentials লোড করো (Load credentials from .env)
load_dotenv()

DB_USER = os.getenv("DB_USER", "root")
DB_PASS = os.getenv("DB_PASS", "")
DB_HOST = os.getenv("DB_HOST", "localhost")
DB_NAME = os.getenv("DB_NAME", "ecl_db")

# SQLAlchemy engine — pymysql driver ব্যবহার করে MySQL-এ কানেক্ট করবে
DATABASE_URL = f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}"
engine = create_engine(DATABASE_URL, echo=False)

# metadata — সব টেবিলের সংজ্ঞা এখানে থাকবে
metadata = MetaData()

# -------- টেবিল ডেফিনিশন (Table definitions) --------

# loans — ঐতিহাসিক লোন ডাটা (Historical loan data for training)
loans = Table(
    "loans", metadata,
    Column("loan_id", Integer, primary_key=True, autoincrement=True),
    Column("age", Integer),
    Column("income", Float),
    Column("loan_amount", Float),
    Column("tenor_months", Integer),
    Column("dti", Float),              # Debt-to-Income ratio
    Column("credit_score", Integer),
    Column("past_due_days", Integer),
    Column("ltv", Float),              # Loan-to-Value ratio
    Column("default_12m", Integer),    # 12 মাসে ডিফল্ট হয়েছে কি না (0/1)
)

# applications — নতুন লোন আবেদন ও সিদ্ধান্ত (New loan applications & decisions)
applications = Table(
    "applications", metadata,
    Column("app_id", Integer, primary_key=True, autoincrement=True),
    Column("created_at", DateTime, server_default=func.now()),
    Column("age", Integer),
    Column("income", Float),
    Column("loan_amount", Float),
    Column("tenor_months", Integer),
    Column("dti", Float),
    Column("credit_score", Integer),
    Column("ltv", Float),
    Column("pd_value", Float),         # Probability of Default
    Column("decision", String(20)),    # Approved / Rejected
)

# ecl_runs — প্রতিটি ECL ব্যাচ রানের সারসংক্ষেপ (ECL batch run summary)
ecl_runs = Table(
    "ecl_runs", metadata,
    Column("run_id", Integer, primary_key=True, autoincrement=True),
    Column("run_date", DateTime, server_default=func.now()),
    Column("total_ead", Float),        # Total Exposure at Default
    Column("total_ecl", Float),        # Total Expected Credit Loss
)

# ecl_results — প্রতিটি লোনের ECL ফলাফল (Per-loan ECL results)
ecl_results = Table(
    "ecl_results", metadata,
    Column("id", Integer, primary_key=True, autoincrement=True),
    Column("run_id", Integer, ForeignKey("ecl_runs.run_id")),
    Column("loan_id", Integer, ForeignKey("loans.loan_id")),
    Column("pd_12m", Float),           # 12-month PD
    Column("pd_life", Float),          # Lifetime PD
    Column("stage", Integer),          # IFRS 9 Stage (1/2/3)
    Column("lgd", Float),             # Loss Given Default
    Column("ead", Float),             # Exposure at Default
    Column("ecl", Float),             # Expected Credit Loss
)


def init_db():
    """ডাটাবেজে টেবিল না থাকলে তৈরি করবে (Create tables if they don't exist)."""
    metadata.create_all(engine)
    print("[OK] All tables created / verified.")


# সরাসরি চালালে টেবিল বানাবে (Create tables if run directly)
if __name__ == "__main__":
    init_db()
