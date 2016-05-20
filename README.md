# Metrol\Route
## A PHP library for routing requests to controllers

This is a no frills HTTP routing library for storing and retrieving routes with various actions associated with them.  Well, there may be a few frills tossed in before I'm ready to call this ready for production.  I'll get into that on the TODO section.

Some basic examples of using this today...

```php
// What the route should do when matched
$action = new \Metrol\Route\Action;
$action->setClass('Controller')
    ->addMethod('doSomething'); // You can call multiple methods in a class
    
$route = new \Metrol\Route('testroute');
$route->setMatchString('/imaroute/')
    ->addAction($action);
```

That code just created a route looking for an HTTP GET (by default) that has a route segment that starts with `/imaroute/`.  Every segment after that one will be considered an argument.

To make all this useful, we'll need to make a deposit to the `Metrol\Route\Bank` so it can be looked up later.

```php
\Metrol\Route\Bank::addRoute($route);
```

Once in the `Bank` you can request it back using the name of the route, or look for it using a `\Metrol\Route\Request` that has all the information needed to help with a match.

```php
$fetchRoute = \Metrol\Route\Bank::getRequestedRoute( new \Metrol\Route\Request );
```

This will automatically use the info from `$_SERVER` to try and match a route.

## Parameters & Arguments

Let's say you want something a bit more specific, like passing some information to the Controller Action from the URL.  Like requesting a view of a specific index value.

`http://www.example.com/view/1234/`

To get a route together to match this, you could use...

```php
$route = new \Metrol\Route('page view route');
$route->setMatchString('/view/:int/')
    ->addAction($action);
```

There are 3 things that are happening here...

1. A requirement that in order to be a match there is an integer in the second segment of the URL.
2. If there's a match the `1234` is automatically applied to the arguments for that route.
3. Any other segments following what was specified will also be added to the list of arguments.

When the `Route\Match` finds a matching route, all the arguments it finds during this process are applied back to the route itself.  So here you can put it all together.

```php
$route = new \Metrol\Route('page view route');
$route->setMatchString('/view/:int/')
    ->addAction($action);

\Metrol\Route\Bank::addRoute($route);

$fetchRoute = \Metrol\Route\Bank::getRequestedRoute( new \Metrol\Route\Request );

$args = $fetchRoute->getArguments()
```

Aside from hints for `:int`, there is also support for `:num` and `:str`.  There's more information about configuring ranges and sizes that I still need to fully document.

## Still TODO

My next addition to this library is to build out a `Dispatch` class that can take that found route and run all the actions assigned to it.  Pretty straight forward stuff.
After that, I'm looking to setup some `Loader` classes that can create routes and deposit them in the `Bank` for you.  I would like to have 3 basic loader types...

1. An INI file.
2. A JSON file.
3. A PHP Controller Class

The third option may be a bit slower to run, but should make for a faster configuration.  Looking to use Reflection on specified classes to examine method names and signatures to produce routes.  More details on this as work progresses.
