import time
import random   # replace with actual sensor reading
import mysql.connector

def read_temperature():
    return round(random.uniform(24, 30), 2)

def read_ph():
    return round(random.uniform(6.5, 7.5), 2)

def read_do():
    return round(random.uniform(5, 9), 2)

while True:
    temp = read_temperature()
    ph = read_ph()
    do = read_do()

    try:
        db = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="watermonitor"
        )
        cursor = db.cursor()
        cursor.execute("INSERT INTO sensor_data (temperature, ph, dissolved_oxygen) VALUES (%s, %s, %s)",
                       (temp, ph, do))
        db.commit()
        db.close()
        print(f"Logged: Temp={temp}, pH={ph}, DO={do}")
    except Exception as e:
        print("DB Error:", e)

    time.sleep(10)  # log every 10 seconds
