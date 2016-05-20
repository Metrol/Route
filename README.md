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
First, it requires that in order to be a match there is an integer in the second segment of the URL.
Secondly, if there's a match the `1234` is automatically applied to the arguments for that route.
Lastly, any other segments following what was specified will also be added to the list of arguments.

When the `Route\Match` finds a matching route, all the arguments it finds during this process are applied back to the route itself.  So here you can put it all together.

```php
$route = new \Metrol\Route('page view route');
$route->setMatchString('/view/:int/')
    ->addAction($action);

\Metrol\Route\Bank::addRoute($route);

$fetchRoute = \Metrol\Route\Bank::getRequestedRoute( new \Metrol\Route\Request );

$args = $fetchRoute->getArguments()
```
