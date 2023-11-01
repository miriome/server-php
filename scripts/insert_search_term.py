import mysql.connector
import csv

mydb = mysql.connector.connect(
  host="184.168.98.206",
  user="miromie_root",
  password="KIaR*~E,L3D.",
  port=3306,
  database="miromie"
)



mycursor = mydb.cursor()

styles = ["Basic", "Korean", "Athleisure", "Hype", "Officewear", "Luxury", "Chic", "Beach", "Boho", "Androgynous", "Emo", "Vintage"]
# styles = ["Basic"]

sql = "INSERT INTO search_terms (base_term, mapped_term) VALUES (%s, %s)"

for style in styles:
  with open("./Associated Keywords to Styles - {}.csv".format(style)) as csv_file:
    brand = ""
    keywords = []
    csv_reader = csv.reader(csv_file, delimiter=',')
    for index, row in enumerate(csv_reader):
      if index == 0: 
        brand = row[1]
      if index > 2:
        if len(row[0]) > 0:
          keywords.append(row[0])
        if len(row) > 1 and len(row[1]) > 0:
          keywords.append(row[1])
    insertionList = [(style, keyword) for keyword in keywords]
    
    try:  
      mycursor.executemany(sql, insertionList)
    except Exception as e:
      print(e)
      print('Something went wrong.')
      
mydb.commit()
        