# cURL

```

$curl = (new cURL())
->post($post)
->cookie($cookie)
->referer($referer)
->useragent($useragent)
->header($header)
->headers($headers);

$contents = $curl->exec($url);

var_dump($curl);

$download = $curl->init()->exec($url, $file);

var_dump($curl);

```

## Author

**Jérôme Taillandier**

## License

This project is licensed under the WTFPL License - see the [LICENSE.md](LICENSE.md) file for details
