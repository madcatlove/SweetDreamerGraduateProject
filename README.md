# SweetDreamer

### Android
Provide
* Member management
* Summary information of sleeping stage
* Use different color for sleep quality on calendar
* Detail sleep information on specific day
* Smart alarm(Trace your sleep stage and wake up when the sleep stage isn't deep)


### Server
Provide
* Several APIs for mobile application and raspberry pi
* Data management

Required
* >= PHP5 (to support laravel4 framework)
* >= MySQL5
* MQTT (to support push message to mobile device)
* Required external program execution permission

### Raspberry pi
Provide
* Collect raw signal data from Neurosky
* It will automatically reconnect to Neurosky when the connection is broken.

Required
* Bluetooth library (to connect Neurosky)
* cURL library (to send collected data to API server)
