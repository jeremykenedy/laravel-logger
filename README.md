# Logger dynamic search

adding dynamic search fields (description , user, URL , method and ip address)

## .env file

#### Base url for server apis 
VUE_APP_AXIOS_BASE_URL=<br>

#### Sentry App key
add these configurations to your .env file to control the logging search
```
LARAVEL_LOGGER_SEARCH_ENABLE=true
LARAVEL_LOGGER_DESCRIPTION_SEARCH=true
LARAVEL_LOGGER_USER_SEARCH=true
LARAVEL_LOGGER_METHOD_SEARCH=true
LARAVEL_LOGGER_ROUTE_SEARCH=true
LARAVEL_LOGGER_IP_ADDRESS_SEARCH=true
```
by default all search fields are enabled when you enable the search with this one line configuration 
```
LARAVEL_LOGGER_SEARCH_ENABLE=true
```
## Authors

* **Mohamed Abouda** - *Initial work* - [GitHub](https://gist.github.com/mohamedAbouda)


