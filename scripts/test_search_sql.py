import mysql.connector
import csv

# 'hostname' => '184.168.98.206',
#         'username' => 'miromie_root',
#         'password' => 'KIaR*~E,L3D.',
#         'database' => 'miromie',
        

mydb = mysql.connector.connect(
  host="184.168.98.206",
  user="miromie_root",
  password="KIaR*~E,L3D.",
  port=3306,
  database="miromie"
)


mycursor = mydb.cursor()
keyword = "Officewear"

sql = """
SELECT *
FROM (
    SELECT UPPER(mapped_term) AS mapped_term_upper
    FROM search_terms
    WHERE UPPER(base_term) LIKE UPPER('%{}%')
) AS uppercaseMappedTerms
""".format(keyword)

mycursor.execute(sql)
results = mycursor.fetchall()
strings = [row[0] for row in results]

orComp = ["OR UPPER(caption) like UPPER('%{}%')".format(string) for string in strings]
orQuery = " ".join(orComp)

main_query = """
SELECT * FROM posts where id in
(SELECT id
FROM posts
WHERE added_by != 0
AND deleted = 0
AND (
    UPPER(caption) LIKE UPPER('%{}%')
    OR UPPER(hashtag) LIKE UPPER('%{}%')
    {}
)
GROUP BY id
) ORDER BY chat_enabled DESC, id DESC ;
""".format(keyword, keyword, orQuery)
print(main_query)


mycursor = mydb.cursor()
mycursor.execute(main_query)
results = mycursor.fetchall()
print(len(results))
# for row in results:
#     print(row)

for row in results:
    for string in strings:
        if string in row[2].upper():
            print('------------------')
            print(row[0], row[2], string)
            print('------------------')
