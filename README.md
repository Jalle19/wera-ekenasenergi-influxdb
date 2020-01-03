# wera-ekenasenergi-influxdb

A tool for parsing electricity consumption data from Ekenäs Energi's Wera service into InfluxDB

## Usage

You'll need to access the Wera online service and check what requests your browser is sending in order to get the 
required parameters (customer code etc.).

Once you have everything, run the following:

```bash
php wera-ekenasenergi-influxdb.php
  --username <username> 
  --password <password> 
  --customerCode <customerCode> 
  --networkCode <networkCode> 
  --meteringPointCode <meteringPointCode>
```

This will simply dump the JSON data to stdout.
