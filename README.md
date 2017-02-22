# Basic usage
Web-application represents 2 pages:
* Main page which contains form with url field
* Statistics page which show basic information about shorten url (e.g. amount of short url usage)

At the main page you should fill the form by valid url data and push submit button.
Application generate short url for you automatically or you can fill appropriate field in above form.
Next you will redirect to page, contains statistics for generated short url.
If same url was generated earlier, you will be redirected to its statistics page, and
doesn't matter what you specified in "desired short url" form field.
# API
Application provides API method for short url creations.
To create url externally, you may send _**POST**_ request to _**/shorten**_ url with 2 parameters:

| Parameter | Description                        | Required |
| --------- | ---------------------------------- | --------:|
| basic_url | Valid url what you want to shorten | true     |
| url_alias | Desired alias                      | false    |

Above parameters must be strings. If arrays will given, only first value will be used.

You will get response with _**JSON**_ data:

| Parameter   | Description       | Type                                    |
| ----------- | ----------------- | ---------------------------------------:|
| errors      | Validation errors | array/undefined(if validation accepted) |
| shorted_url | Created alias     | string/undefined(if validation denied)  |

# Install application
To install application on your server you need to follow this instructions:
* Pull repository
```
git pull https://github.com/Alex-Kane/url_shortener.git
```
* Configure application in _app/config/parameters.yml_
* Update database
```
php app/console doctrine:schema:update --force
```
#Commands
Application contains console command to remove all expired urls from database (which created
earlier than 15 days ago) which you may desire to schedule by crontab
```
php app/console app:remove-expired-urls
```