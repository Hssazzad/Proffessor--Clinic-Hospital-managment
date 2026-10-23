# Credit Risk App — Loan Approval + IFRS 9 ECL

## বর্ণনা (Description)
Streamlit-ভিত্তিক ওয়েব অ্যাপ যেটা:
- লোন আবেদন মূল্যায়ন করে (Approve / Reject)
- IFRS 9 অনুযায়ী Expected Credit Loss (ECL) হিসাব করে

## সেটআপ (Setup)
```bash
cd ecl-app
python -m venv venv
venv\Scripts\activate      # Windows
pip install -r requirements.txt
```

## চালানো (Run)
```bash
streamlit run app.py
```

## প্রযুক্তি (Tech Stack)
- Python, Streamlit, Pandas, Scikit-learn
- MySQL (XAMPP), SQLAlchemy, PyMySQL
- Plotly (চার্ট / visualization)
