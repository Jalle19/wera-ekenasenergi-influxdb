# wera-ekenasenergi-influxdb

A set of small tools for parsing electricity consumption data from Ekenäs Energi's Wera service into InfluxDB. 

Ultimately it enables you to produce Grafana graphs such as this:

![Grafana example graph](https://raw.githubusercontent.com/Jalle19/wera-ekenasenergi-influxdb/master/grafana_example_graph.png)

The query used by the graph looks like this:

![Grafana example graph](https://raw.githubusercontent.com/Jalle19/wera-ekenasenergi-influxdb/master/grafana_example_query.png)

## Usage

This project comes with two tools:

* `dump-json` simply performs the authentication dance and dumps the complete JSON consumption data to stdout
* `influxdb-import` imports the JSON to InfluxDB

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

### influxdb-import

This command takes dumped JSON from stdin and feeds it into InfluxDB like this:

```
Consumptions PowerConsumption=1.450000 1578085200000000000
Consumptions PowerConsumption=1.190000 1578088800000000000
Consumptions PowerConsumption=2.620000 1578092400000000000
Consumptions Temperature=3.700000 1572566400000000000
Consumptions Temperature=3.700000 1572570000000000000
Consumptions Temperature=3.800000 1572573600000000000
```

Usage:

```bash
php influxdb-import.php
  --influxDbUrl <influxDbUrl> 
  --influxDbName <influxDbName> 
  --influxDbUsername <influxDbUsername> 
  --influxDbPassword <influxDbPassword>
  < <jsonFile>
```

## License

GNU GENERAL PUBLIC LICENSE version 3.0
