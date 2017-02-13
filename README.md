# Google Tag Manager DataLayer for Wordpress

Google Tag Manager DataLayer plugin put all wordpress info from platform, woocommerce,
user-agent, analytics utm and more on your GTM DataLayer.

## How to Install

Just download the latest version from Github here: https://github.com/luizeof/wp-gtm-datalayer/archive/master.zip

Important: This plugin is not listed on Wordpress directory until release of first stable version.

## Usage

After install and activate the plugin your Google Tag Manager Data Layer it's full of info on head tag.

Go to Google Data Layer Panel and Enable Preview Mode to see all variables available on your Data Layer:

### Wordpress Variables

`gtmdlPagePostType`:
Return the post type

`gtmdlPageTemplate`:
Return the post template

`gtmdlPageCategory`:
Return the main posy category

`gtmdlPageTags`:
Return the all post tags with commas

`gtmdlPagePostAuthorID`:
Return the ID of author of post/page

`gtmdlPagePostAuthor`:
Return the Author name of post/page

`gtmdlPagePostDate`:
Return the post publish date

`gtmdlPagePostDateYear`:
Return the year of post publish date

`gtmdlPagePostDateMonth`:
Return the month of post publish date

`gtmdlPagePostDateDay`:
Return the day of post publish date

`gtmdlUserEmail`:
Return the current user e-mail

`gtmdlUserType`:
Return the current user role

`gtmdlUserId`:
Return the current user ID

`gtmdlLogin`:
Return the status "logged" or "anonymous"

### User Agent / Browser Variables

`gtmdlBrowserName`
Return the Browser name

`gtmdlBrowserVersion`
Return the browser version

`gtmdlBrowserEngineName`

`gtmdlBrowserEngineVersion`

`gtmdlOsName`

`gtmdlOsVersion`

`gtmdlDeviceType`

`gtmdlDeviceManufacturer`

`gtmdlDeviceModel`

`gtmdlReferer`

### GeoLocation Variables

`gtmdlGeoCountry`

`gtmdlGeoCountryCode`

`gtmdlGeoRegion`

`gtmdlGeoRegionName`

`gtmdlGeoCity`

`gtmdlGeoTimezone`

`gtmdlGeoISP`


### Google Analytics UTM Variables

`gtmdlUtmSource`

`gtmdlUtmMedium`

`gtmdlUtmCampaign`

`gtmdlUtmTerm`

`gtmdlUtmContent`

### WooCommerce Variables

`gtmdlWooOrdersCount`
