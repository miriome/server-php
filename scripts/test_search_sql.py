import mysql.connector
import csv

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="winson",
  port=3306,
  database="miromie-local"
)


mycursor = mydb.cursor()


sql = """
SELECT *
FROM (
    SELECT UPPER(mapped_term) AS mapped_term_upper
    FROM search_terms
    WHERE UPPER(base_term) LIKE UPPER('%athleisure%')
) AS uppercaseMappedTerms
"""

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
    UPPER(caption) LIKE UPPER('%athleisure%')
    OR UPPER(hashtag) LIKE UPPER('%athleisure%')
    {}
)
GROUP BY id
) ORDER BY chat_enabled DESC, id DESC ;
""".format(orQuery)



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

sql3 = '''
(SELECT id
FROM posts
WHERE added_by != 0
AND deleted = 0
AND (
    UPPER(caption) LIKE UPPER('%athleisure%')
    OR UPPER(hashtag) LIKE UPPER('%athleisure%')
    {}
)
GROUP BY id
) '''

mycursor = mydb.cursor()
mycursor.execute(main_query)
results = mycursor.fetchall()

print([row[0] for row in results])
print(len([row[0] for row in results]))