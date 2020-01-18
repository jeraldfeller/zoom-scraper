@ECHO OFF
FOR /L %%A IN (1,1,50000) DO (
    C:\wamp64\bin\php\php7.2.18\php.exe "C:\wamp64\www\upwork\flora\zoominfo-scraper\scrape.php" -a=search
)
PAUSE