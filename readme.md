#SpaceGDN Bridge

Usage Examples:

```php
$bridge = new Mcprohosting\Spacegdn\Bridge();

// Sets the location of the GDN
$bridge->setEndpoint('gdn.api.xereo.net');

// Gets all jars:
$results = $bridge->get('jars');

// Gets the second page of jars:
$results = $bridge->get('jars')->page(2);

// Gets all versions owned by "foo":
$results = $bridge->get('versions')->belongingTo('foo');

// Query demonstrating most GDN properties.
$results = $bridge
    ->get('builds')
    ->where('build', '$gt', 1234)
    ->orderBy('build', 'desc')
    ->page(3);

// Results can be iterated over like an array:
foreach ($results as $result) {
    echo $result->checksum . "\n";
}

// Can be turned into json, explicitly or implicitly as a string (both do the same thing):
echo $results->toJson();
echo $results;

// And also can be accessed by property:
print_r($results->pages);
```
