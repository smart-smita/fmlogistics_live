import pandas as pd

def import_excel(file_path, sheet_name=0):
    # Read Excel file (sheet_name=0 means first sheet)
    df = pd.read_excel(file_path, sheet_name=sheet_name)

    # Loop through rows
    for index, row in df.iterrows():
        # Print all columns in the row
        print(dict(row))  

if __name__ == "__main__":
    file_path = r"D:\workspace-unitglo\new alerts\Operational Excellence 3.0 Database_Alert.xlsx"
    import_excel(file_path)
