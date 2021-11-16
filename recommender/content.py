import pymysql
import pandas as pd
import numpy as np
import sys


recipeID = int(sys.argv[1])
amount = int(sys.argv[2])

conn = pymysql.connect(host='h2774525.stratoserver.net',
                       user='dataintegration',
                       password='gis7&B85',
                       db='dataintegration',
                       charset='utf8')
curs = conn.cursor()

sql_ing = 'SELECT recipeID, NDB_No,NDB_name FROM ingredients WHERE (measure_algorithm > 0.5) AND recipeID IN (SELECT recipeID FROM all_merged_dataset_with_id_copy_and_priority WHERE priority = 1)'
sql_ing2 = 'SELECT recipeID, name FROM ingredients WHERE (measure_algorithm <= 0.5) AND recipeID IN (SELECT recipeID FROM all_merged_dataset_with_id_copy_and_priority WHERE priority = 1)'
tdata = pd.read_sql(sql_ing, conn)
bdata = pd.read_sql(sql_ing2, conn)


def editdist(p_1, p_2):
    if len(p_1) > len(p_2):
        p_1, p_2 = p_2, p_1

    dist = range(len(p_1) + 1)
    for i2, c2 in enumerate(p_2):
        dist2 = [i2+1]
        for i1, c1 in enumerate(p_1):
            if c1 == c2:
                dist2.append(dist[i1])
            else:
                dist2.append(1 + min((dist[i1], dist[i1 + 1], dist2[-1])))
        dist = dist2
    return dist[-1]


def table(r_id, sdata):
    input_data = sdata[sdata.recipeID == r_id]
    out1 = []
    out2 = []

    for i in input_data.NDB_No:
        m_id = sdata.recipeID[sdata.NDB_No == i]
        m_id = m_id.drop_duplicates()
        for j in m_id:
            out1.append(j)
            out2.append(i)
    out_data = pd.DataFrame({'recipeID': out1, 'NDB_No': out2})
    out_data = out_data.drop(index=out_data.index[out_data.recipeID == r_id])
    return out_data


def recommend(r_id, amount, sdata, ndata):
    result_table = table(r_id, sdata)
    c_table = pd.DataFrame(result_table.recipeID.value_counts())
    r = list(c_table.index[c_table.recipeID == max(c_table.recipeID)])
    if len(r) > amount:
        ing = ndata[ndata.recipeID == r_id]
        compare = []
        for recipe in range(1, len(r)+1):
            ing2 = ndata[ndata.recipeID == r[recipe-1]]
            ing2 = ing2.reset_index(drop=True)
            temp = 0
            for index, row in ing.iterrows():
                ing1 = pd.DataFrame({'name': np.tile(row['name'], len(ing2))})
                df = pd.concat([ing1['name'], ing2['name']], axis=1, keys=['ing', 'ing2'])
                df['dist'] = np.vectorize(editdist)(df['ing'], df['ing2'])
                temp += df['dist'].min()
            compare.append(temp)
        while len(r) > amount:
            r.pop(compare.index(max(compare)))
            compare.remove(max(compare))
    if len(r) < amount:
        for i in range(1, max(c_table.recipeID) + 1):
            x = max(c_table.recipeID) - i
            y = list(c_table.index[c_table.recipeID == x])
            if len(r) + len(y) > amount:
                ing = ndata[ndata.recipeID == r_id]
                compare = []
                for recipe in range(1, len(y) + 1):
                    ing2 = ndata[ndata.recipeID == y[recipe - 1]]
                    ing2 = ing2.reset_index(drop=True)
                    temp = 0
                    for index, row in ing.iterrows():
                        ing1 = pd.DataFrame({'name': np.tile(row['name'], len(ing2))})
                        df = pd.concat([ing1['name'], ing2['name']], axis=1, keys=['ing', 'ing2'])
                        df['dist'] = np.vectorize(editdist)(df['ing'], df['ing2'])
                        temp += df['dist'].min()
                    compare.append(temp)
                while len(r) < amount:
                    r.append(y[compare.index(min(compare))])
                    compare.remove(min(compare))
            else:
                for j in y:
                    r.append(j)
            if len(r) >= amount: break
    return r


result = recommend(recipeID, amount, tdata, bdata)
print(result)
