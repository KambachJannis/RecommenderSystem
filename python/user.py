import pymysql
import pandas as pd
import random
import sys


if sys.argv[1] != "none":
    recipeID = int(sys.argv[1])
    rating = int(sys.argv[2])
    userID = int(sys.argv[3])
else:
    recipeID = "none"
    rating = "none"
    userID = int(sys.argv[3])

conn = pymysql.connect(host='h2774525.stratoserver.net',
                       user='dataintegration',
                       password='gis7&B85',
                       db='dataintegration',
                       charset='utf8')


def save(p_recipeid, p_rating, p_user, p_conn):
    success = True
    rating = -1+((p_rating-1)/2)
    cursor = p_conn.cursor()
    sql = ('SELECT NDB_No FROM ingredients WHERE measure_algorithm > 0.5 AND recipeID = %d' % p_recipeid)
    data = pd.read_sql(sql, p_conn)
    for index, row in data.iterrows():
        tsql = ('SELECT rating, times_rated FROM user_ingredients WHERE NDB_no = %d AND userID = %d' % (row['NDB_No'], p_user))
        rows = cursor.execute(tsql)
        tdata = cursor.fetchall()
        if rows < 1:
            wsql = ('INSERT INTO user_ingredients VALUES (%d, %d, %d, %d)' % (p_user, row['NDB_No'], rating, 1))
            write = cursor.execute(wsql)
            p_conn.commit()
        else:
            timesrated = tdata[0][1] + 1
            trating = ((tdata[0][0] * (timesrated - 1)) + rating) / timesrated
            wsql = ('UPDATE user_ingredients SET rating = %f, times_rated = %d WHERE NDB_no = %d AND userID = %d' %
                    (trating, timesrated, row['NDB_No'], p_user))
            write = cursor.execute(wsql)
            p_conn.commit()
    fsql = ('INSERT INTO user_history VALUES (%d, %d, %d)' % (p_user, p_recipeid, rating))
    write = cursor.execute(fsql)
    p_conn.commit()
    return success


def recommend(p_user, p_conn):
    cursor = p_conn.cursor()
    sql = ('SELECT DISTINCT recipeID FROM ingredients WHERE NDB_No IN (SELECT NDB_No FROM user_ingredients WHERE userID = %d) AND '
           'recipeID NOT IN (SELECT recipeID FROM user_history WHERE userID = %d) AND recipeID IN '
           '(SELECT recipeID FROM all_merged_dataset_with_id_copy_and_priority WHERE priority = 1)' % (p_user, p_user))
    data = pd.read_sql(sql, p_conn)
    data = list(data['recipeID'])
    placeholder = ', '.join(map(str, data))
    print(placeholder)
    sql = ('SELECT recipeID, GROUP_CONCAT(NDB_No), COUNT(recipeID) FROM ingredients WHERE recipeID IN (%s) GROUP BY recipeID' % placeholder)
    cursor.execute(sql)
    data = cursor.fetchall()
    rsql = ('SELECT NDB_No, rating FROM user_ingredients WHERE userID = %d' % p_user)
    rdata = pd.read_sql(rsql, conn)
    if len(data) > 3000:
        data = random.sample(data, 3000)
    compare = []
    for recipe in data:
        df1 = pd.DataFrame({'NDB_No': recipe[1].split(',')})
        df1['NDB_No'] = df1['NDB_No'].astype(int)
        matched = df1.merge(rdata, how='inner', on='NDB_No')
        compare.append([recipe[0], matched['rating'].sum()/recipe[2]])
    compare = sorted(compare, key=lambda x: x[1], reverse=True)
    result = compare[0][0]
    return result


if recipeID != "none":
    save(recipeID, rating, userID, conn)  # 1=recipeID, 2=rating, 3=userID
result = recommend(userID, conn)
print(result)
