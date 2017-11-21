# Google Tag Manager Data Layer by Powertic

Google Tag Manager DataLayer plugin put all wordpress info from platform, woocommerce,
user-agent, analytics utm and more on your GTM DataLayer.

## How to Install

Just download the latest version from Github here: https://github.com/powerticmkt/wp-gtm-datalayer/archive/master.zip

Important: This plugin is not listed on Wordpress directory until release of first stable version.

## Usage

After install and activate the plugin your Google Tag Manager Data Layer it's full of info on head tag.

Go to Google Data Layer Panel and Enable Preview Mode to see all variables available on your Data Layer:

### Wordpress Variables

`gtmPagePostType`:
Return the post type

`gtmPageTemplate`:
Return the post template

`gtmPageCategory`:
Return the main posy category

`gtmPageTags`:
Return the all post tags with commas

`gtmPagePostAuthorID`:
Return the ID of author of post/page

`gtmPagePostAuthor`:
Return the Author name of post/page

`gtmPagePostDate`:
Return the post publish date

`gtmPagePostDateYear`:
Return the year of post publish date

`gtmPagePostDateMonth`:
Return the month of post publish date

`gtmPagePostDateDay`:
Return the day of post publish date

`gtmUserEmail`:
Return the current user e-mail

`gtmUserType`:
Return the current user role

`gtmUserId`:
Return the current user ID

`gtmLogin`:
Return the status "logged" or "anonymous"

### User Agent / Browser Variables

`gtmBrowserName`
Return the Browser name

`gtmBrowserVersion`
Return the browser version

`gtmBrowserEngineName`

`gtmBrowserEngineVersion`

`gtmOsName`

`gtmOsVersion`

`gtmDeviceType`

`gtmDeviceManufacturer`

`gtmDeviceModel`

`gtmReferer`

### GeoLocation Variables

`gtmGeoCountry`

`gtmGeoCountryCode`

`gtmGeoRegion`

`gtmGeoRegionName`

`gtmGeoCity`

### Google Analytics UTM Variables

`gtmUtmSource`

`gtmUtmMedium`

`gtmUtmCampaign`

`gtmUtmTerm`

`gtmUtmContent`

### WooCommerce Variables

`gtmWooOrdersCount`
