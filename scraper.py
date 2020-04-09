#!/usr/bin/python3
import sys, os, os.path
import requests
import re
import datetime

ZUPANIJE = [
    'bjelovarsko-bilogorska',
    'brodsko-posavska',
    'dubrovacko-neretvanska',
    'grad-zagreb',
    'istarska',
    'karlovacka',
    'koprivnicko-krizevacka',
    'krapinsko-zagorska-zupanija',
    'licko-senjska',
    'medjimurska',
    'osjecko-baranjska',
    'pozesko-slavonska',
    'primorsko-goranska',
    'sibensko-kninska',
    'sisacko-moslavacka',
    'splitsko-dalmatinska',
    'varazdinska',
    'viroviticko-podravska',
    'vukovarsko-srijemska',
    'zadarska',
    'zagrebacka',
]

RE_ZARAZENI = re.compile(r"""<text class='zarazeni' data-url='https://www.koronavirus.hr/([a-z-]+)/\d+' x='\d+' y='\d+' stroke='transparent' text-anchor='middle' dy='\d+.\d+em' style='font-size: \d+px;'>(\d+)</text>""")
RE_IZLIJECENI = re.compile(r"""<text class='izlijeceni' data-url='https://www.koronavirus.hr/([a-z-]+)/\d+' x='\d+' y='\d+' stroke='transparent' text-anchor='middle' dy='\d+.\d+em' style='font-size: \d+px;'>(\d+)</text>""")

r = requests.get('https://koronavirus.hr/')
if r.status_code != 200:
    print("Error getting web page:", r.status_code)
    sys.exit(0)

zarazeni = { x: 0 for x in ZUPANIJE }

for data in RE_ZARAZENI.findall(r.text):
    zarazeni[data[0]] = int(data[1])

izlijeceni = { x: 0 for x in ZUPANIJE }

for data in RE_IZLIJECENI.findall(r.text):
    izlijeceni[data[0]] = int(data[1])

today = datetime.date.today()

zarazeni_exists = os.path.exists("zarazeni.csv")
with open("zarazeni.csv", "at") as f:
    if not zarazeni_exists:
        f.write('"datum", ')
        f.write(", ".join([ '"%s"' % x for x in ZUPANIJE]))
        f.write("\n")
    f.write('"%d-%02d-%02d"' % (today.year, today.month, today.day))
    for zup in ZUPANIJE:
        f.write(", %s" % zarazeni[zup])
    f.write("\n")

izlijeceni_exists = os.path.exists("izlijeceni.csv")
with open("izlijeceni.csv", "at") as f:
    if not izlijeceni_exists:
        f.write('"datum", ')
        f.write(", ".join([ '"%s"' % x for x in ZUPANIJE]))
        f.write("\n")
    f.write('"%d-%02d-%02d"' % (today.year, today.month, today.day))
    for zup in ZUPANIJE:
        f.write(", %s" % izlijeceni[zup])
    f.write("\n")

