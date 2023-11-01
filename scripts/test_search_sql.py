import mysql.connector
import csv

mydb = mysql.connector.connect(
  host="localhost",
  user="root",
  password="winson",
  port="3306",
  database="miromie-local"
)

mycursor = mydb.cursor()
keyword = "basic"
userId = 9
cte_query = """
    WITH uppercaseMappedTerms AS (
        SELECT
            UPPER(mapped_term) AS mapped_term_upper
        FROM
            search_terms
        WHERE
            UPPER(base_term) LIKE UPPER(%s)
    )
    """

    # Your main query
main_query = """
SELECT posts.caption
FROM posts
WHERE added_by != %s
AND deleted = 0
AND (
    UPPER(caption) LIKE UPPER(%s)
    OR UPPER(hashtag) LIKE UPPER(%s)
    OR EXISTS (
        SELECT 1
        FROM uppercaseMappedTerms
        WHERE FIND_IN_SET(
            UPPER(%s),
            REPLACE(mapped_term_upper, ' ', ',')
        )
    )
)
ORDER BY chat_enabled DESC, id DESC;
"""
full_query = cte_query + main_query

mycursor.execute(full_query, (keyword, userId, keyword, keyword, keyword))
results = mycursor.fetchall()

for row in results:
        # Process the results here
        print(row)