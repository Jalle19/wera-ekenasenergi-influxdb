# wera-ekenasenergi-influxdb

A tool for parsing electricity consumption data from Ekenäs Energi's Wera service into InfluxDB

## Usage

This project comes with two tools:

* `dump-json` simply performs the authentication dance and dumps the complete JSON consumption data to stdout
* TODO: Tool for importing the JSON to InfluxDB

### dump-json

You'll need to access the Wera online service and check what requests your browser is sending in order to get the 
required parameters (customer code etc.).

Once you have everything, run the following:

```bash
php dump-json.php
  --username <username> 
  --password <password> 
  --customerCode <customerCode> 
  --networkCode <networkCode> 
  --meteringPointCode <meteringPointCode>
```
