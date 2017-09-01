# jQuery Easy Ticker plugin

jQuery easy ticker is a news ticker like plugin, which scrolls the list infinitely. It is highly customizable, flexible with lot of features and works in all browsers.

## Demo

[Live plugin demo](http://www.aakashweb.com/demos/jquery-easy-ticker/) | [Plugin Home page](http://www.aakashweb.com/jquery-plugins/easy-ticker/)

## Features

* Two directions available (Up and down).
* Can be targeted on any template.
* Flexible API for extending to various applications.
* Supports 'easing' functions.
* Mouse pause feature available.
* The speed of the transition can be changed.
* Controls can be added inorder to Play/pause or move the list Up and down.
* Cross browser support.
* Light-weight (4 Kb - Full source / 2.7 Kb - Minified source).

## Syntax

Include jQuery and jQuery easy ticker in the source and use the 

**HTML**

target --> parent --> children
 
```HTML
<div class="myWrapper">
	<ul>
		<li>List element 1</li>
		<li>List element 2</li>
		<li>List element 3</li>
		<li>List element 4</li>
	</ul>
</div>

or

<div class="myWrapper">
	<div>
		<div>Element 1</div>
		<div>Element 2</div>
		<div>Element 3</div>
		<div>Element 4</div>
	</div>
</div>
```
**jQuery**

```JavaScript
$('.myWrapper').easyTicker({
	// list of properties
});
```

[Demo](http://www.aakashweb.com/demos/jquery-easy-ticker/)

## Documentation

Plugin's documentation is written in its official home page. Check it out in [this link](http://www.aakashweb.com/jquery-plugins/easy-ticker/)

## Requirements

* jQuery 1.7+

## License

Copyright (c) 2014 [Aakash Chakravarthy](http://www.aakashweb.com/), released under the MIT License.