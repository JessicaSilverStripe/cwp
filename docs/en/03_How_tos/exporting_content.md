title: Exporting content
summary: Using the RESTful API to access and export site data.

# Exporting content

This how-to will aid a developer wanting to export content from a SilverStripe site running on CWP. All exporting is
done with the [`silverstripe/restfulserver`](https://github.com/silverstripe/silverstripe-restfulserver) module which is enabled by default. 
Here we provide details on gaining access to specific data attributes via REST. Further documentation is available in 
the module's documentation on [GitHub](https://github.com/silverstripe/silverstripe-restfulserver/blob/master/README.md).

## Fetching content

To browse all instances of a DataObject make the call `GET /api/v1/(ClassName)` to the website. This will return an XML
array in the format:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ArrayList totalSize="10">
    <Page href="http://mysite.com/api/v1/Page/1">
        <Title>My Page</Title>
        <Content><p>Hello, World!</p></Content
        ...
    </Page>
    ...
</ArrayList>
```

As you might imagine, this could reach a very large size and take a long time to generate. To help with this, the API
supports the `length` and `start` parameters which you can use to restrict the size of the block of content that you
are retrieving. So you can make multiple calls, like so:

	GET /api/v1/Page?start=0&length=10
	GET /api/v1/Page?start=10&length=10
	GET /api/v1/Page?start=20&length=10

You can repeat these calls, incrementing the `start` parameter each time, until you've looped through the entire list.
The `totalSize` attribute of the root `ArrayList` node will always show the length of the full list, not just the size
of the cut-down list you're seeing right now.

## Exposing new properties

The list of properties that is exported from `Page` is restricted by the static array `$api_access` in `BasePage.php`.
If you have added any properties or relations that you wish to include in this list, you'll need to redefine this
static in the relevant classes and extend the array to include your new properties.

So if you have added

```php
use SilverStripe\Assets\Image;

class Page 
{
    private static $db = [
        'Author' => 'Varchar(255)'
    ];
    private static $has_one = [
        'Thumbnail' => Image::class
    ];
}
```

then you might always want to add:

```php
private static $api_access = ['Locale', 'URLSegment' ... 'ParentID', 'ID', 'Author', 'Thumbnail'];
```
This will include the extra fields in the XML result.

## Exposing existing objects

Sometimes there might be a class that you wish to expose to the API but that you don't want to modify. Modules are a
good example of this - say you have the `userforms` module and you want to expose form submissions. Sure, you could
modify `SubmittedForm.php` and `SubmittedFormField.php` and add `$api_access = true;` in there, but that would create
hassles when it came to upgrading the module.

A better way to do it is with extensions. If you created these two class definitions:

```php
class SubmittedFormExtension extends DataExtension 
{
    private static $api_access = true;
}
```

```php
class SubmittedFormFieldExtension extends DataExtension 
{
    private static $api_access = true;
}
```

and then added this to your `mysite/_config/config.yml`:

```yaml
SilverStripe\UserForms\Model\Submission\SubmittedForm:
  extensions:
   - SubmittedFormExtension
SilverStripe\UserForms\Model\Submission\SubmittedFormField:
  extensions:
   - SubmittedFormFieldExtension
```

Now the `SubmittedForm` and `SubmittedFormField` classes are exposed to the REST API without modifying them directly.

## Fetching relations

### Has one

Has ones are exposed through a property on the owning object that contains the ID of the owned object. So if page has
an image called "Portrait", then there will be both a "PortraitID" and a "Portrait" field on Page. The "PortraitID"
field contains just the ID. The "Portrait" field is an empty node that contains three attributes: `href` that points to
a request URL that just displays that one field, `id` which is the same ID that is in the "Portrait" field, and
`linktype` which will be set to "has_one" in this case. An example is below:

```xml
<Page>
    <PortraitID>100</PortraitID>
    <Portrait href="http://mysite.com/api/v1/Page/1/Portrait.xml" id="100" linktype="has_one"/>
</Page>
```

If this is not appearing, check that you've exposed the relation as specified in the "Exposing new properties" section.

### Has many

Has many relationships can only be viewed as both sides of the relationship are exposed to the REST API. They appear in
the following format:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ArrayList totalSize="10">
    <Page href="http://mysite.com/api/v1/Page/1">
        ...
        <RelatedPages href="http://mysite.com/api/v1/Page/1/RelatedPages.xml" linktype="has_many">
            <Page href="http//mysite.com/api/v1/Page/2" id="2" />
            ...
        </RelatedPages>
        ...
    </Page>
    ...
</ArrayList>
```

If you browse to the location in the `href` attribute (`http://mysite.com/api/v1/Page/1/RelatedPages.xml` in this case)
then you'll get the same content but without the additional attributes of the page.

From the other side of the relationship it will appear as a has_one relationship.

### Many many

Many many relationships appear exactly like a has many except the `linktype` is "many_many", and it's a list on both
sides of the relationship.

## Disabling the RESTful server

Out of the box the REST API is enabled giving access to already publicaly published pages as reusable data in XML or JSON standards.
If you wish to disable this feature you can add the following into the `mysite/_config/config.yml`.

```yaml
CWP\CWP\PageTypes\BasePage:
  api_access: false
```
