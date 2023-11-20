# Forge

Un liviano y básico router php para proyectos rápidos y pequeños.

**Tabla de contenidos**

- [Configure](#configure)
- [Routing](#routing)
- [Routes](#routes)
  - [Wildcards](#wildcards)
- [Routes Group](#routes-group)
- [Controllers](#controllers)
- [Add namespaces](#add-namespaces)
- [Engine](#engine)
  - [Application Engine](#application-engine)
  - [Json Engine](#json-engine)
- [Dependencies Container](#dependencies-container)
  - [The `Injector` class](#the-injector-class)
- [Services Provider](#services-provider)
  - [The `Services` class](#the-services-class)
- [Container vs Services](#container-vs-services)
- [Uri Generator](#uri-generator)
- [Request](#request)
- [Response](#response)
  - [Json Response](#json-response)
  - [Redirect Response](#redirect-response)
- [Client Request](#client-request)
- [Emitter](#emitter)
- [Data Collection](#data-collection)
  - [The `Bag` class](#the-bag-class)
  - [The `Arguments` class](#the-arguments-class)
  - [The `Globals` class](#the-globals-class)
- [Views](#views)
  - [Template](#template)
  - [Arguments](#arguments)
  - [Extending the template](#extending-the-template)
  - [Render](#render)
- [Configurator](#configurator)
- [DB Connection](#db-connection)
  - [Connecting using an URL](#connecting-using-an-url)
  - [Auto connect](#auto-connect)

- [Handler](#handler)
- [Users](#users)
- [Functions](#functions)


## Configure

En **Apache** edita el archivo `.htaccess`  en la raíz del proyecto:

```htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Handle Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

AddDefaultCharset utf-8
```

Para **Nginx** agrega lo siguiente en las configuraciones del servidor:

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```

Por último genera el **autoload [^1]**

```shell
composer dump-autoload
```

## Routing

El proceso de enrutamiento consiste en registrar un conjunto de rutas que serán comparadas con el `REQUEST_URI` del lado del cliente (*web browser*) representado por un objeto `Request` (Ver [Request](#request)). Si una ruta coincide se ejecuta la acción asociada con esta, es decir un método de una clase (*controlador*)  que devolverá una respuesta representada por un objeto de `Response` (Ver [Response](#response)). Si la URI solicitada no corresponde con ninguna de las rutas registradas, el router lanzará un <mark>`RouteNotFoundException`</mark>, en cambio si el método de petición no es soportado por el router lanzará un <mark>`UnsupportedRequestMethodException`</mark>.

El método `Router::handleRequest` procesa el Request, hace el enrutamiento y devuelve un objeto Response que es enviado al cliente (Ver [Emitter](#emitter)).

```php
use Application\Http\FooController;
use rguezque\Forge\Exceptions\RouteNotFoundException;
use rguezque\Forge\Router\{
    Emitter,
    Request,
    Response,
    Route,
    Router
};

require __DIR__.'/vendor/autoload.php';

$router = new Router;

$router->addRoute(new Route('GET', 'index', '/', FooController::class, 'indexAction'));

$router->addRoute(new Route('GET', 'hola_page', '/hola/{nombre}', FooController::class, 'holaAction'));

$router->addRouteGroup('/foo', function(RouteGroup $group) {
    $group->addRoute(new Route('GET', 'foo_index', '/', FooController::class, 'fooAction'));
    $group->addRoute(new Route('GET', 'foo_bar', '/bar', FooController::class, 'barAction'));
});

try {
    $response = $router->handleRequest(Request::fromGlobals());
} catch(RouteNotFoundException $e) {
    $response = new Response(
        sprintf('<h1>Not Found</h1>%s', $e->getMessage()),
        404
    );
} catch(UnsupportedRequestMethodException $e) {
    $response = new Response(
        sprintf('<h1>Method Not Allowed</h1>%s', $e->getMessage()), 
        405
    );
}
Emitter::emit($response);
```

## Configure router

El router puede recibir como parámetros un array asociativo con las opciones disponibles de configuración. Las opciones disponibles son:

- `'set.basepath'`: Especifica un directorio base en caso de que el router este alojado en un subdirectorio de la raíz del servidor.

- `'set.supported.request.methods'`: Define un *array* que sobrescribe los métodos de petición http permitidos por el router. Por default el router acepta `GET` y `POST`.

- `'set.views.path'`: *(Opcional)* Especifica un directorio global donde se buscarán por default los archivos de *templates* para las vistas, ya sea para rutas que renderizan vistas (`RouteView`) o bien para la clase `View`. 

  **Nota:** Si se define un nuevo directorio de *templates* al crear un objeto de `View`  este tendrá preferencia sobre el directorio global.


```php
use rguezque\Forge\Router\Router;

$router = new Router([
    'set.basepath' => '/myapp',
    'set.supported.request.methods' => ['GET', 'POST', 'PUT'],
    'set.viewspath' => __DIR__.'/templates'
]);
//$router->addRoute(...)
//...
```

## Routes

El método `Router::addRoute` permite agregar una ruta. Una ruta se representa por una instancia de `Route`. Esta clase recibe cinco parámetros obligatorios; el método de petición, un nombre único para la ruta, el *string path*, el nombre del controlador y el nombre del método a ejecutar para dicha ruta. Por default los únicos métodos aceptados por el router son `GET` y `POST` (Ver [Configurator](#configurator) si requieres definir o agregar tus propios métodos http de petición aceptados.).

Por ejemplo si una ruta recibirá una petición `POST`:

```php
$router->addRoute(new Route('POST', 'save_article', '/article/save', BlogController::class, 'saveNewAction'));
```

El router también acepta dos rutas con el mismo *string path* pero diferente método de petición, nombre y acción a ejecutar.

```php
$router->addRoute(new Route('GET', 'show_articles', '/articles', BlogController::class, 'saveNewAction'));
$router->addRoute(new Route('POST', 'save_articles', '/articles', BlogController::class, 'updateAction'));
```

Para definir rutas que solo devuelven una vista, sin tener que definir un controlador, se envía una instancia de `RouteView`. Recibe 4 parámetros, el nombre de la ruta, la definición de la ruta, la ruta a la plantilla y opcionalmente un *array* asociativo con argumentos a pasar a dicha plantilla.

```php
$router->addRoute(new RouteView('main_home', '/', __DIR__.'/views/homepage.php'));

$router->addRoute(new RouteView('hello_view', '/hello/{name}', __DIR__.'/views/hello.php'));

$router->addRouteGroup('/foo', function(RouteGroup $group) {
    $group->addRoute(new RouteView('login_from', '/', __DIR__.'/views/login_form.php', ['action'=>'/foo/login']));
    $group->addRoute(new Route('foo_login', '/foo/login', FooController::class, 'helloAction'));
});
```

**Nota:** Si previamente se ha definido el directorio de *templates* en la configuración no es necesario especificar la ruta completa, simplemente el nombre del template (Ver [Configurator](#configurator)).

### Wildcards[^2]

Si una ruta tiene *wildcards*, se recuperan en un objeto `Bag` (Ver [The Bag Class](#the-bag-class)) a través del método `Request::getParameters` y pueden ser tomados a través de `Bag::get` usando como clave el parámetro nombrado con que fueron definidos en la ruta. Si la ruta tiene *wildcards* como expresiones regulares (RegEx) se recuperan con la clave `@matches` que devuelve un array lineal con los valores enumerados en orden de *match*.

```php
//index.php
//...
$router->addRoute(new Route(
    'GET', 
    'saludo',
    '/hola/(\w+)/(\w+)',
    FooController::class,
    'holaAction'
));
$router->addRoute(new Route(
    'GET', 
    'hello_page',
    '/hello/{name}/{lastname}',
    FooController::class,
    'helloAction'
));
//...

// FooController.php
//...
public function holaAction(Request $request, Response $response): Response {
	$args = $request->getParameters();
	list($nombre, $apellido) = $args->get('@matches')
    return $response->withContent(sprintf('Hola %s %s', $nombre, $apellido));
}

public function helloAction(Request $request, Response $response): Response {
	$args = $request->getParameters();
	$nombre = $args->get('name');
    $apellido = $args->get('lastname');
    return $response->withContent(sprintf('Hola %s %s', $nombre, $apellido));
}
//...
```

## Routes group

El método `Router::addRouteGroup` permite crear un grupo de rutas bajo un mismo *prefijo de ruta*. Especifica el prefijo y seguidamente un *closure* con un parámetro `RouteGroup` que permite definir las rutas. Cada ruta heredará el prefijo definido.

```php
$router->addRouteGroup('/foo', function(RouteGrup $group) {
    $group->addRoute(new Route('GET', 'foo_index', '/', FooController::class, 'holaAction'));
    $group->addRoute(new Route('GET', 'foo_hello_page', '/hello/{nombre}', FooController::class, 'helloAction'));
    $group->addRoute(new Route('GET', 'foo_bar_page', '/bar/entry/{id}', FooController::class, 'entryAction'));
});
```

Lo anterior genera las rutas:

```
/foo/
/foo/hello/{nombre}
/foo/bar/entry/{id}
```

## Controllers

Los controladores deben ser solo clases instanciables (no *closures*[^3], funciones, objetos o métodos estáticos). Para una mejor lectura del código los nombres de controladores deben tener el sufijo `Controller` y los métodos el sufijo `Action`, de lo contrario lanzará un `BadNameException`.

Los controladores reciben un parámetro `Request` y un `Response` dependiendo del motor de funcionamiento del router (Ver [Engine](#engine)) y devolverán un `Response` o un `array` según sea el caso. 

```php
// index.php
//...
$router = new Router;
$router->addRoute(new Route('GET', 'hola_page', '/hola/{nombre}/{apellido}', FooController::class, 'holaAction'));
//...
```

```php
// app/Http/FooController.php
namespace App\Http;

use rguezque\Forge\Router\Request;
use rguezque\Forge\Router\Response;

class FooController {

    public function holaAction(Request $request, Response $response): Response {
        $args = $request->getParameters();
        $message = sprintf('Hola %s %s', $args->get('nombre'), $args->get('apellido'));
        $response->clear()->withContent($message);

        return $response;
    }
}
```

Para recuperar los argumentos como un array asociativo usa el método `Bag::all`, donde cada clave de dicho array corresponde al nombre de cada *wildcard* de la ruta:

```php
//...
$args = $request->getParameters();
$params = $args->all() // Devuelve los parámetros en un array asociativo nombre-valor
```

Si se hace una petición `GET` en la URI solicitada (ejem. `/path/?foo=bar&lorem=ipsum`), serán accesibles en `$_GET` con el método `Request::getQueryParams`. 

```php
// Los valores "foo" y "lorem" del ejemplo anterior se recuperan en un objeto Bag con:
$params = $request->getQueryParams()
```

## Using attributes to define routes

Esta forma utiliza los atributos de las clases y los métodos para generar y agregar rutas y grupos de rutas. El método `Router::addControllersWithAttributes` recibe como argumento un _array_ de nombres de controladores previamente con atributos definidos, en caso contrario no registrará ninguna ruta.

```php
use App\MainController;
use rguezque\Forge\Exceptions\RouteNotFoundException;
use rguezque\Forge\Exceptions\UnsupportedRequestMethodException;
use rguezque\Forge\Router\Emitter;
use rguezque\Forge\Router\Injector;
use rguezque\Forge\Router\Request;
use rguezque\Forge\Router\Router;
use rguezque\Forge\Router\Response;

require __DIR__.'/vendor/autoload.php';

$app = new Router;

// Aqui se cargan los conntroladores
$app->addControllersWithAttributes([
    MainController::class
]);

$container = new Injector;
$container->add(MainController::class);

$engine->setContainer($container);
$app->setEngine($engine);

try {
    $response = $app->handleRequest(Request::fromGlobals());
} catch (RouteNotFoundException $e) {
    $response = new Response($e->getMessage(), 404);
} catch (UnsupportedRequestMethodException $e) {
    $response = new Response($e->getMessage(), 405);
}

Emitter::emit($response);
```

Estos atributos deben tener una definición específica. Para el caso de las rutas, el controlador debería verse así:

```php
namespace App;

use rguezque\Forge\Router\Request;
use rguezque\Forge\Router\Response;
// Se debe incluir la clase RouteAttribute pues de aqui se recuperan 
// los parámetros para crear la ruta
use rguezque\Forge\Router\Attributes\RouteAttribute;

class MainController {

    #[RouteAttribute('GET', 'index_page', '/')]
    public function indexAction(Request $request, Response $response) {
        return $response->send('Hola mundo!');
    }
    
    #[RouteAttribute('GET', 'about_page', '/about')]
    public function indexAction(Request $request, Response $response) {
        return $response->send('Hola mundo!');
    }
}
```

Por cada método se define uno o más atributos `RouteAttribute` que debe contener los valores: método de petición de la ruta, nombre de la ruta y el _string path_ de la ruta. Para el caso de los grupos de rutas, se debe definir un atributo `GroupAttribute` para la clase, el cual recibe como valor el prefijo del grupo de rutas:

```php
namespace App;

use rguezque\Forge\Router\Request;
use rguezque\Forge\Router\Response;
// Se debe incluir la clase GroupAttribute y RouteAttribute pues de 
// aqui se recuperan los parámetros para crear el grupo de rutas
use rguezque\Forge\Router\Attributes\GroupAttribute;
use rguezque\Forge\Router\Attributes\RouteAttribute;

#[GroupAttribute('/foo')]
class MainController {

    #[RouteAttribute('GET', 'index_page', '/')]
    public function indexAction(Request $request, Response $response) {
        return $response->send('Hola mundo!');
    }
    
    #[RouteAttribute('GET', 'about_page', '/about')]
    public function indexAction(Request $request, Response $response) {
        return $response->send('Hola mundo!');
    }
}
```

De esta forma tenemos un grupo con un prefijo que heredarán cada una de las rutas que se generen a partir de cada método que tenga atributos de ruta definidos, en caso contrario serán ignorados estos métodos; de la misma forma las clases que no tengan definido atributos de grupo solo se crearán las rutas de forma normal por cada método.

## Engine

El router tiene dos motores de funcionamiento, `ApplicationEngine` (usado por default) y `JsonEngine` , este último permite usar el router como una API. En el primer caso los métodos de cada controlador reciben dos parámetros, `Request` y `Response` y cada método debe devolver un `Response` de lo contrario lanzará un `UnexpectedValueException`. Si una ruta contiene *wildcards* estos son enviados en el `Request` y recuperados con `Request::getParameters`, mientras que el parámetro`Response` proporciona los métodos para generar una respuesta.

En el segundo caso, `JsonEngine` exige que se retorne un *array* asociativo en cada método de los controladores. El router se encarga de convertirlo a formato `json` y generar el respectivo `JsonResponse`.

El motor de funcionamiento se asigna al router con el método `Router::setEngine` que recibe un objeto `EngineInterface`. No es obligatorio definir un motor de funcionamiento, a menos que se use un Contenedor (Ver [Dependencies Container](#dependencies-container)) o un Proveedor de Servicios (Ver [Services Provider](#services-provider)), o bien, se requiera devolver datos en JSON.

### Application Engine

```php
// index.php
//...
$router->addRoute(new Route('GET', 'hola_page', '/hola/{nombre}', FooController::class, 'holaAction'));
$app = new ApplicationEngine();
$router->setEngine($app);

try {
    $response = $router->handleRequest(Request::fromGlobals());
} catch(RouteNotFoundException $e) {
    $response = Response::create(404)
    ->withContent(
        sprintf('<h1>Not Found</h1>%s', $e->getMessage())
    );
}
Emitter::emit($response);
```

```php
// app/Http/FooController.php
//...
public function holaAction(Request $request, Response $response): Response {
    $nombre = $request->getParameter('nombre');
    $response->clear()->withContent(sprintf('Hola %s', $nombre));

    return $response;
}
```

### Json Engine

```php
// index.php
//...
$router->addRoute(new Route('GET', 'show_page', '/show/{id}', FooController::class, 'showAction'));
$app = new JsonEngine();
$router->setEngine($app);

try {
    $response = $router->handleRequest(Request::fromGlobals());
} catch(RouteNotFoundException $e) {
    $response = Response::create(404)
    ->withContent(
        sprintf('<h1>Not Found</h1>%s', $e->getMessage())
    );
}
Emitter::emit($response);
```

```php
// app/Http/FooController.php
//...
public function showAction(Request $request): array {
    $id = $request->getParameter('id');
    //...
    $data = [
        'id' => $id,
        'title' => 'Lorem ipsum',
        'author' => 'John Doe'
    ];

    return $data;
}
```

## Dependencies Container

La clase `Injector` permite crear un contenedor de dependencias. A cada motor de funcionamiento del router (`ApplicationEngine` o `JsonEngine`) se le puede asignar un contenedor con el método `EngineInterface::setContainer` desde el cual se buscarán los controladores (*class controller*) y demás dependencias que serán inyectados al constructor de cada clase. Si no se asigna un contenedor el router generara una instancia de cada controlador con `ReflectionClass::newInstance` asumiendo que no deben inyectarse dependencias.

```php
//...
$container = new Injector;
$container->add(FooController::class);

$app = new ApplicationEngine();
$app->setContainer($container);
$router->setEngine($app);

try {
    $response = $router->handleRequest(Request::fromGlobals());
} catch(RouteNotFoundException $e) {
    $response = Response::create(404)
    ->withContent(
        sprintf('<h1>Not Found</h1>%s', $e->getMessage())
    );
}

Emitter::emit($response);
```

### The `Injector` Class

Esta clase permite crear contenedores e inyectar dependencias. Cuenta con cinco métodos:

- `Injector::add`: Recibe el nombre de la dependencia y el nombre de la clase a instanciar así como los parámetros a inyectar. Si solo se envía la clase a instanciar, se toma el nombre de la clase como el nombre de dicha dependencia. También se permite agregar un `Closure` como dependencia, en cuyo caso es obligatorio asignar un nombre.
- `Injector::addParameter`: Permite agregar un parámetro a una clase agregada al contenedor.
- `Injector::addParameters`: Permite agregar varios parámetros a una clase agregada al contenedor a través de un array.
- `Injector::get`: Recupera una dependencia por su nombre. Opcionalmente puede recibir como segundo argumento un *array* con argumentos (válgase la redundancia) utilizados por la dependencia solicitada, esto es útil cuando la dependencia es una función cuyo resultado dependerá de parámetros enviados al momento de llamarla. En el caso de que la dependencia sea una clase instanciada, estos argumentos se inyectarán al final, de igual forma es útil cuando la clase recibirá algunos argumentos que podrían ser opcionales o cuyo valor dependerá de la programación al momento de solicitarla.
- `Injector::has`: Verifica si existe una dependencia por su nombre.

También se puede usar como métodos estáticos a través del *facade* `Container`.

```php
Container::add(FooController::class);
Container::add(PDO::class)->addParameters(/*...*/);

$engine = new ApplicationEngine;
$engine->setContainer(Container::app());
```


## Services Provider

La clase `Services` permite crear un proveedor de servicios. A cada motor de funcionamiento del router (`ApplicationEngine` o `JsonEngine`) se le puede asignar un proveedor con el método `EngineInterface::setServices` desde el cual se podrá tener acceso en toda la aplicación. La diferencia con un contenedor es que el proveedor de servicios inyecta los servicios registrados a cada **método** de un *controller class* y todos los servicios son accesibles en toda la aplicación, mientras que el contenedor solo inyecta las dependencias agregadas al **constructor** de cada clase especificada y solo están disponibles estas dependencias en dicha clase que se inyectan.

### The `Services` Class

Esta clase permite registrar servicios y solo dispone de dos métodos, `Services::register` que recibe dos parámetros, un alias para el servicio y un closure con el servicio a devolver, y el método `Services::has` que devuelve `true` si un servicio especificado existe. Para acceso a un servicio simplemente se invoca como un método del objeto `Services` (método mágico`Services::__call`) o bien como una propiedad en contexto de objeto (método mágico `Services::__get`). Ejemplo:

```php
$services = new Services;
$services->register('pi_const', function() {
    return 3.141592654;
});

$engine = new ApplicationEngine();
$engine->setServices($services)

$router = new Router;
$router->addRoute(new Route('GET', 'index', '/', FooController::class, 'indexAction'));

$router->setEngine($engine);
$router->handleRequest(Request::fromGlobals());
```

```php
// FooController::indexAction
public function indexAction(Request $request, Response $response, Services $service): Response {
    // Se verifica que exista y se recupera el servicio como un método
    $pi = $services->has('pi_const') ? $services->pi_const() : 3.14;
    // O se recupera el servicio como propiedad en contexto de objeto
    //$pi = $services->pi_const;
    return $response->withContent($pi);
}
```

## Container vs Services

Solo se puede implementar uno a la vez, o se elige usar un contenedor de dependencias (`EngineInterface::setContainer`) o bien el proveedor de servicios (`EngineInterface::setServices`). Si se intenta utilizar ambos el que sea asignado en última instancia sobrescribirá al primero.

## URL Generator

Esta clase permite hacer *reverse routing* (generar la URL de cada ruta a partir de su nombre y string de la ruta asociada).

Al crear una instancia de `UrlGenerator` se envía como argumento los nombres y URL de las rutas proporcionado por `Router::getRouteNames`.

```php
$router = new Router;
$router->addRoute(/*...*/);
// ...
$generator = new UrlGenerator($router->getRouteNames());
```

Para generar una URL se invoca el método estático `UrlGenerator::reverseRouting`, que recibe el nombre de la ruta y los parámetros para la ruta si es necesario. Los parámetros se envían en un array donde cada clave debe llamarse igual que cada *wildcard* definido en la ruta.

```php
// Al defnir la ruta
$router->addRoute(new Route('GET', 'hola_page', '/hola/{nombre}', MyController::class, 'holaAction'));

UrlGenerator::reverseRouting('hola_page', ['nombre' = 'John']);
// Generará la URL "/hola/John"
```

## HTTP

### Request

La clase `Request` representa una petición HTTP del lado del servidor. Los métodos disponibles son los siguientes:

- `fromGlobals()`: Método estático que crea un `Request` a partir de los globales `$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES`, y un *array* vacío para los parámetros nombrados de las rutas. Los *getters* devuelven un objeto `Bag` (Ver [The Bag Class](#the-bag-class)).
- `getQueryParams()`: Devuelve los parámetros de `$_GET`.
- `getBodyParams()`: Devuelve los parámetros de `$_POST`.
- `getPhpInputStream()`: Devuelve el contenido del *stream* de solo lectura `php://input` a través de un objeto `PhpInputStream`. Dispone de dos métodos: `PhpInputStream::getParsedStr` que devuelve los parámetros de la petición en un array asociativo asbtraido en un objeto `Bag`; y `PhpInputStream::getDecodedJson` que sirve para recuperar parámetros enviados en formato *json* a través de peticiones *ajax*.
- `getServerParams()`: Devuelve los parámetros de `$_SERVER`.
- `getCookieParams()`: Devuelve los parámetros de `$_COOKIE`.
- `getUploadedFiles()`: Devuelve los parámetros de `$_FILES`.
- `getParameters()`: Devuelve los parámetros nombrados de una ruta.
- `getParameter(string $parameter, $default = null)`: Devuelve un parámetro nombrado de una ruta, y si no existe devolverá el valor default especificado.
- `withQueryParams(array $query)`: Agrega a `Request` parámetros `$_GET` especificados.
- `withBodyParams(array $body)`: Agrega a `Request` parámetros `$_POST` especificados.
- `withServerParams(array $server)`: Agrega a `Request` parámetros `$_SERVER` especificados.
- `withCookieParams(array $cookies)`: Agrega a `Request` parámetros `$_COOKIE` especificados.
- `withUploadedFiles(array $files)`: Agrega a `Request` parámetros `$_FILES` especificados.
- `withParameters(array $parameters)`: Agrega a `Request` parámetros nombrados de una ruta.
- `withParameter(string $name, $value)`: Agrega a  `Request` un parámetro nombrado.
- `withoutParameter(string $name)`: Elimina de `Request` un parámetro de ruta especifico.
- `buildQueryString(string $url, array $params)`: Genera una cadena de petición GET (similar a `build_query_string`, ver [functions](#functions)).

### Client Request

La clase `ClientRequest` representa peticiones HTTP desde el lado del cliente.

```php
use rguezque\Forge\Router\ClientRequest;

// Si se omite el segundo parámetro se asume que será una petición GET
$request = new ClientRequest('https://jsonplaceholder.typicode.com/posts', 'POST');
// Se envía la petición y se recupera la respuesta
$response = $request->send();
```

Métodos disponibles:

- `withRequestMethod(string $method)`: Especifica el tipo de petición que se hará (`GET`, `POST`, `PUT`, `DELETE`).
- `withHeader(string $key, string $value)`: Agrega un encabezado a la petición.
- `withHeaders(array $headers)`: Agrega múltiples encabezados a la petición, recibe un array asociativo como parámetro, donde cada clave es un encabezado seguido de su contenido.
- `withPostFields($data, bool $encode = true)`: Agrega parámetros a la petición mediante un array asociativo de datos que es convertido a formato JSON.
- `withBasicAuth(string $username, string $password)`: Agrega un encabezado `Authorization` basado en un nombre de usuario y contraseña simples.
- `withTokenAuth(string $token)`: Agrega un encabezado `Authorization` basado en JWT.
- `getInfo()`: Devuelve un `array` asociativo con información sobre la petición enviada. Si se invoca antes de `ClientRequest::send()` devolverá `null`.
- `send()`: Envía la petición.

### Response

Representa una respuesta HTTP del servidor.

- `create(int $code = 200, string $phrase = '')`: Método estático que crea un simple response con un código y texto de estatus (opcional).
- `getStatus()`: Devuelve el código de estatus HTTP actual.
- `getContent()`: Devuelve el cuerpo del response.
- `getstatusText()`: Devuelve el texto de estatus HTTP actual.
- `getProtocolVersion()`: Devuelve la versión de protocolo HTTP actual del servidor.
- `withContent(string $content)`: Especifica el contenido a mostrar.
- `withHeader(string $key, string $value)`: Especifica un encabezado HTTP y su valor.
- `withHeaders(array $headers)`: Especifica varios encabezado HTTP a la vez y sus valores.
- `withStatus(int $code)`: Especifica un código numérico de estatus HTTP. Ver [HTTP Status Codes](http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml).
- `withStatusPhrase(string $phrase)`: Especifica un texto a asignar al actual código de estatus HTTP.
- `withProtocolVersion(string $version)`: Especifica que versión de protocolo HTTP usar. Por lo regular es '1.1'.
- `clear()`: Limpia el response actual, reiniciando a los valores default.

#### Json Response

Extiende a la clase `Response`, por default recibe un array asociativo y devuelve una respuesta en formato de datos JSON. Si los datos que recibe `JsonResponse` ya están en formato JSON previamente, se debe especificar un segundo parámetro `false`, para evitar el intento de convertir los datos.

```php
$data = [
    'id' => $id,
    'name' => 'John Doe',
    'age' => 30
];
return new JsonResponse($data);
```

#### Redirect Response

Extiende a la clase `Response` y devuelve una respuesta de redirección a la URI especificada. Si se usa `UriGenerator` se puede crear la URI de las rutas incluyendo las que tienen *wildcards* y enviarla como argumento (Ver [Uri Generator](#urigenerator)).

```php
return new RedirectResponse('/hola/John/Doe');
```

### Emitter

Esta clase solo contiene el método estático `Emitter::emit`, y recibe como parámetro un objeto `Response`. Se encarga de "emitir" el response.

```php
use rguezque\Forge\Router\{Emitter, Router, Reques};

//...
$response = $router->handleRequest(Request::fromGlobals());
Emitter::emit($response);
```

## Data Collection

Ambas clases, `Bag` y `Arguments`,  sirven para manipular una colección de datos (array asociativo). Sin embargo tienen diferencias, la clase `Bag` solo contiene métodos de lectura, es decir, no permite modificar los datos, mientras que `Arguments` extiende a la clase `Bag` y es de lecto escritura, es decir, permite leer y modificar los datos.

### The `Bag` Class

Esta clase contiene métodos de solo lectura, es decir, solo puede evaluar y consultar los parámetros que almacena.

- `get(string $key)`: Recupera un parámetro por nombre. También se puede recuperar un parámetro en contexto de objeto gracias al método mágico `Bag::__get`.
- `all()`: Devuelve el array de parámetros.
- `has(string $key)`: Devuelve `true` si un parámetro existe.
- `valid(string $key)`: Devuelve `true` si un parámetro no esta vació y no tiene valor `null`.
- `count()`: Devuelve la cantidad de parámetros almacenados.
- `keys()`: Devuelve un *array* lineal con todos los nombres de los parámetros, es decir, las claves del *array* asociativo de parámetros.
- `gettype(string $key)`: Devuelve el tipo de dato de un parámetro.

### The `Arguments` Class

Esta clase *extiende* a la clase padre `Bag` heredando sus métodos y además contiene métodos de escritura, es decir, puede crear, modificar y eliminar los parámetros que almacena.

- `set(string $key, $value)`: Permite crear o sobrescribir un parámetro de la colección de datos. También se puede crear un parámetro en contexto de objeto gracias al método mágico `Arguments::__set`.
- `remove(string $key)`: Elimina un parámetro por su nombre.
- `clear()`: Elimina todos los parámetros.

### The `Globals` Class

Almacena y proporciona acceso a las variables de `$GLOBALS` mediante métodos estáticos. Tiene los mismos métodos de `Bag` y `Arguments`, excepto `Bag::keys` y `Bag::gettype`

## Views

Las vistas son el medio por el cual el router devuelve y renderiza un objeto `Response` con contenido HTML en el navegador. La única configuración que se necesita es definir el directorio en donde estarán alojados los archivos *templates*. 

**Nota:** Si previamente de ha definido el directorio de *templates* en la configuración no es necesario especificarlo en el constructor de la clase `View` (Ver [Configurator](#configurator)), aunque si se define un directorio aquí, este tendrá prioridad sobre la configuración inicial.

```php
use rguezque\Forge\Router\View;

$view = new View(
    __DIR__.'/mis_plantillas', // Directorio donde se alojan los templates
);
```

La configuración inicial de `View` puede ser sobrescrita con el método `View::setPath`.

```php
$view->setPath(__DIR__.'/templates');
```

### Template

El método que permite definir un *template* principal es `View::template` , este puede recibir uno o dos parámetros; el primer parámetro es el nombre del archivo *template* y el segundo es un array asociativo con argumentos que se envían al *template*.

```php
// app/Http/FooController.php
function __construct(View $view) {
    $this->view = $view;
}

public function homeAction(Request $request, Response $response): Response {
    $result = $this->view->template('home.php', ['message' => 'Hola mundo!'])->render();
    return $response->withContent($result);
}
```

### Arguments

Una forma alternativa de enviar argumentos a una vista es a través de los métodos `View::addArgument` y `View::addArguments`. El primero recibe dos parámetros (nombre y valor) y el segundo un array asociativo. Estos parámetros serán automáticamente incluidos al invocar el método `View::render`, por lo cual deben ser declarados antes de renderizar (Ver [Render](#render)).

```php
$view->addArgument('message', 'Hello weeerld!');
$view->addArguments([
    'id' => 1,
    'name' => 'Banana',
    'color' => 'yellow'
]);
```

### Extending the template

Para extender un template se utiliza el método `View::extendWith`, este método recibe tres parámetros; el nombre del template que extenderá al template principal, un alias único con el que se incluirá en el template principal y opcionalmente un *array* de argumentos que se envian al template que está extendiendo al principal.

```php
$data = [
    'home': '/',
    'about': '/about-us',
    'contact': '/contact-us'
];
// Se guarda el template menu.php con el alias 'menu_lateral' y se le envian parámetros en la variable $data
$view->template('index.php', ['title' => 'Ejemplo de vistas']);
$view->extendWith('menu', 'menu_lateral', $data);
$view->render();
```

```php
//menu.php
// Recibe los parámetros enviados en $data
<nav>
    <ul>
        <li><a href="<?= $home ?>">Home</a></li>
        <li><a href="<?= $about ?>">About</a></li>
        <li><a href="<?= $contact ?>">Contact</a></li>
    </ul>
</nav>
```

```php
// index.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
</head>
<body>
    <?php
        // Imprime en pantalla el contenido de menu.php guardado previamente con el alias 'menu_lateral'
        echo $menu_lateral
    ?>
</body>
</html>
```

### Render

El método `View::render` se invoca siempre al final y devuelve lo contenido en el actual *buffer* para ser recuperado en una variable y enviado en un `Response`.

## DB Connection

La clase `DbConnection` proporciona el medio para crear una conexión *singleton* con MySQL a través del driver `PDO` o la clase `mysqli`. El método estático `DbConnection::getConnection` recibe los parámetros de conexión y devuelve un objeto con la conexión creada dependiendo del parámetro `driver` donde se define si se utilizara por default MySQL con `PDO` o con `mysqli`.

```php
use rguezque\Forge\Router\DbConnection;

$db = DbConnection::getConnection([
    // 'driver' => 'mysqli',
    'driver' => 'mysql', // Se usa PDO
    'host' => 'localhost',
    'port' => 3306,
    'user' => 'root',
    'pass' => 'mypassword',
    'dbname' => 'mydatabase'
    'charset' => 'utf8'
]);
```

### Connecting using an URL

Otra alternativa es usar una *database URL* como parámetro de conexión, a través del método estático `DbConnection::dsnParser`; este recibe una URL y la procesa para ser enviada a `DbConnection::getConnection` de la siguiente forma:

```php
use rguezque\Forge\Router\DbConnection;

// Con mysqli
// 'mysqli://root:mypassword@127.0.0.1/mydatabase?charset=utf8'
// Con PDO
$connection_params = DbConnection::dsnParser('mysql://root:mypassword@127.0.0.1/mydatabase?charset=utf8');
$db = DbConnection::getConnection($connection_params);
```

### Auto connect

El método estático `DbConnection::autoConnect` realiza una conexión a MySQL tomando automáticamente los parámetros definidos en un archivo `.env`. 

```php
use rguezque\Forge\Router\DbConnection;

$db = DbConnection::autoConnect();
```

El archivo `.env` debería verse mas o menos así:

```
DB_DRIVER="mysqli"
DB_NAME="mydatabase"
DB_HOST="127.0.0.1"
DB_PORT=3306
DB_USER="root"
DB_PASS="mypassword"
DB_CHARSET="utf8"
```

**Nota:** Se debe usar alguna librería que permita procesar la variables almacenadas en `.env` y cargarlas en las variables `$_ENV`.

## Handler

Esta clase se encarga de configurar el manejador de errores tanto en modo <u>*production*</u> como <u>*development*</u>, así como la zona horaria para el manejo correcto de fechas en PHP. Recibe un array asociativo con tres parámetros: `log_path`, `environment` y `timezone`; no importa el orden en que se declaren. Debe declararse al inicio, antes que todo en el controlador frontal. Las configuraciones se aplican con solo crear una instancia de `Handler` o con el método estático `Handler::configure` que de igual forma recibe los parámetros ya mencionados.

```php
use rguezque\Forge\Router\Handler;

new Handler([
    'log_path' => __DIR__.'/var/logs',
    'timezone' => 'America/Mexico_City',
    'environment' => 'development' // Cambiar a 'production' para puesta en marcha (deploy)
]);
```

## Users

La clase `Users` recibe 2 parámetros, una conexión PDO y un array opcional que define los nombres de la tabla y campos donde se buscara a los usuarios. Por default se buscara por usuarios en la tabla `users` donde el campo de usuario deberá llamarse `username` y el campo de la contraseña deberá llamarse `password`. Sin embargo puede definir su propio nombre de la tabla de usuarios así como los campos de usuario y contraseña, ya sea al momento de crear la instancia de `Users` o con los métodos: `setUsersTable`, `setIdentityField`, `setCredentialField` después de creada la instancia.

Usando el contenedor:

```php
$container = new Injector;
$container->add(PDO::class)->addParameters([
    'mysql:dbname=ejemplo;host=localhost;port=3306;charset=utf8',
    'usuario',
    'contrasena'
]);
// Los parámetros se inyectan en el orden en que son declarados
$container->add(Users::class)->addParameter(PDO::class)->addParameter([
    'tablename' => 'usuarios',
    'identity_field' => 'nombre_usuario',
    'credential_field' => 'contrasena'
]);
```

Usando servicios también se puede definir desde el constructor pero se muestra de esta forma a modo de ejemplo de como llamar a los métodos  `setUsersTable`, `setIdentityField` y `setCredentialField`:

```php
$services = new Services();

$services->register('pdo', function() {
	return new PDO('mysql:host=localhost;port=3306;dbname=test;charset=utf8', 'usuario', 'contrasena');
});
$services->register('users', function() use($services) {
    $pdo = $services->pdo();
    $users = new Users($pdo);
	$users->setUsersTable('mis_usuarios');
	$users->setIdentityField('email');
	$users->setCredentialField('contrasena');
    
    return $users;
});

```

**Nota:** La clase `Users` también dispone del método público `Users::findUser` el cual es utilizado internamente por el controlador encargado de hacer el *login*. Este método ejecuta la consulta en la base de datos; si encuentra el usuario y coincide la contraseña, devuelve un array asociativo con los datos encontrados relacionados al usuario, de lo contrario devolverá un array vacío. De esta forma la clase `Users` puede ser reutilizada para otros proyectos.

## Functions

El router dispone de ciertas funciones que se invocan bajo el namespace `Forge\functions`. Ejemplo:

```php
use function rguezque\Forge\functions\str_ends_with;

str_ends_with('FooBar', 'Bar') // Devuelve true
```

Se incluyen las siguientes:

- `add_trailing_slash(string $str)`: Añade una barra diagonal al final de una cadena de texto.
- `remove_trailing_slash(string $str)`: Remueve las barras diagonales al final de una cadena de texto.
- `add_leading_slash(string $str)`: Añade una barra diagonal al inicio de una cadena de texto.
- `remove_leading_slash(string $str)`: Elimina las barras diagonales al inicio de una cadena de texto.
- `str_starts_with(string $haystack, string $needle)`: Devuelve `true` si una cadena de texto tiene un prefijo específico.
- `str_ends_with(string $haystack, string $needle)`: Devuelve `true` si una cadena de texto tiene un sufijo específico.
- `str_prepend(string $subject, string ...$prepend)`: Concatena una o varias cadenas de texto al inicio de otra cadena de texto principal. La primera declarada es la primera en ser concatenada y así sucesivamente.
- `str_append(string $subject, string ...$append)`: Concatena una o varias cadenas de texto al final de otra cadena de texto principal.
- `str_path(string $path)`: Utilizada por el router, aplica un formato válido de ruta para ser procesado en el *routing*. Elimina *slashes* al final y agrega uno al inicio.
- `is_assoc_array(mixed $value)`: Devuelve `true` si un argumento es un array asociativo y no lineal.
- `json_file_get_contents(string $file)`: Lee el contenido de un archivo `.json` y lo devuelve como un array asociativo en php.
- `unsetcookie(string $name)`: Elimina una cookie.
- `equals(string $strone, string $strtwo)`: Devuelve `true` si dos cadenas de texto son equivalente o iguales.
- `str_to_pascalcase(string $str)`: Convierte una cadena de texto a formato *PascalCase*.
- `url_exists(string $url)`: Devuelve `true` si una URL existe.
- `str_random($length = 20, $special_chars = true, bool $more_entropy = false)`: Genera una cadena de texto aleatoriamente, con una longitud definida (Por default es de 20).
- `dd($var)`: Vuelca información de una variable en texto preformateado para una mejor lectura de su cóntenido y termina el script actual.
- `build_query_string(string $url, array $params)`: Genera una cadena de petición GET.

[^1]: Al final del proyecto utiliza el autoloader optimizado `composer dump-autoload -o`
[^2]: Un wildcard es uno o varios parámetros que se definen en la ruta, pueden tener un nombre asignado entre llaves `{}` o bien, ser definidos como expresiones regulares; en ambos casos estos harán *match* con la petición que se haga a través del navegador web.
[^3]: Funciones anónimas, es decir, no tienen un nombre especificado y permiten acceder al ámbito de una función externa.
