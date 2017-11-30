# PHP URL Object

An URL object to parse, manipulate or create URLs with a fluent interface.

The class exposes all components of common Web URLs as public properties:

```PHP
$url = new Url('https://peter%40example.com:pass@domain.tld:8080/all+products/search?query=all#fragment');
$url->scheme->get(); // -> "https"
$url->host->get(); // -> "domain.tld"
$url->port->get(); // -> 8080
$url->credentials->username; // peter@example.com
$url->credentials->password;
$url->path->get(); // "all products/search"
$url->path->filename(); // "search"
$url->query->get('query'); // "all"
$url->fragment->get(); // "fragment"
$url->getUrl(); 
``` 

URL manipulation:

```PHP
$url->clearHost(); // remove the scheme, host, port, credentials
$url->clearPath(); // remove the path, query, fragment
$url->clear(Url::QUERY | Url::FRAGMENT); // remove only specific components

// replace components with components from another URL
$url->replace('http://example.com/index.html', Url::SCHEME);
$url->replacePath('http://example.com/index.html');

$url = new Url('../styles/main.css');
$url->makeAbsolutePath('http://domain.tld/products/search'); // -> /styles/main.css
$url->makeAbsolute('http://domain.tld/products/search'); // -> http://domain.tld/styles/main.css

```


Path manipulation:

```PHP
$url->path->set('automatically encöded/')
$url->path->normalize(); // resolves /foo/../bar to /bar
$url->filename(); // returns just the filename
```

Query manipulation:

```PHP
$url->query->set('text', 'encöded');
$url->query->has('text');
$url->query->replace([
	'a' => 'foo', 
	'b' => 'bar'
]);
foreach( $url->query as list($key, $values) ) {
	print $key . ": " . join(', ', $values) . "\n"; 
}
```

Credentials manipulation:

```PHP
$url->credentials->username = 'peter@example.com';
$url->credentials->password = 'pass;
$url->credentials->clear(); // removes username and password if present
$url->crdentials->isEmpty(); // Checks whether a username and/or password is present.
$url->crdentials->equals($otherUrl->credentials); // True if both are empty or both are same.
```


Comparison:

```PHP
$url->equals($otherUrl);
$url->equals('/all+products/search', Url::PATH);
$url->scheme->equals($otherUrl->scheme);
```


Common methods of all components:

```PHP
->isEmpty(); // is the component present?
->clear(); // makes the component empty
->equals($otherurl->component); 
```
