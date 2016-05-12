[![Latest Stable Version](https://poser.pugx.org/bankiru/rpc-server-bundle/v/stable)](https://packagist.org/packages/bankiru/rpc-server-bundle) 
[![Total Downloads](https://poser.pugx.org/bankiru/rpc-server-bundle/downloads)](https://packagist.org/packages/bankiru/rpc-server-bundle) 
[![Latest Unstable Version](https://poser.pugx.org/bankiru/rpc-server-bundle/v/unstable)](https://packagist.org/packages/bankiru/rpc-server-bundle) 
[![License](https://poser.pugx.org/bankiru/rpc-server-bundle/license)](https://packagist.org/packages/bankiru/rpc-server-bundle)

[![Build Status](https://travis-ci.org/bankiru/rpc-server-bundle.svg)](https://travis-ci.org/bankiru/rpc-server-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bankiru/rpc-server-bundle/badges/quality-score.png)](https://scrutinizer-ci.com/g/bankiru/rpc-server-bundle/)
[![Code Coverage](https://scrutinizer-ci.com/g/bankiru/rpc-server-bundle/badges/coverage.png)](https://scrutinizer-ci.com/g/bankiru/rpc-server-bundle/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/53b98f25-6b08-43b2-9ebe-e2d83e17b868/mini.png)](https://insight.sensiolabs.com/projects/53b98f25-6b08-43b2-9ebe-e2d83e17b868)

# HTTP RPC Server bundle

This bundle provides default controller realisation to handle RPC 
requests which come to the application via HTTP requests
 
## Implementations

RPC server does not declares any implementation requirements. Some could be

* JSON-RPC
* SOAP (extends XML-RPC)

or other custom RPC which operates with method+parameters and utilizes single
endpoint for several methods
 
## HTTP Endpoints

Endpoint is a HTTP route which process basic HTTP request, providing initial parsing
and processing request data

### Configuration

Basic endpoint configuration looks like

```yaml
rpc:
  router:
    endpoints:
      my-public-endpoint:
        path: /
        defaults:
          _controller: JsonRpcBundle:JsonRpc:jsonRpc
          _format: json
        resources:
        - "@MyBundle/Resources/config/service_rpc.yml"
```

This creates endpoint on URL / with generic symfony controller. Also it 
pre-populates the methods from the `service_rpc.yml` config file
 
### Method routing

Each RPC request has method and parameters. You can configure the application
to handle different methods within different endpoints with different actions

Generic configuration looks like

```yaml
my_bundle:
  resource: "@MyBundle/Rpc/"
  prefix: my_bundle/
  type: annotation
```

Different resource types are supported. Built-in are

#### Annotation 

```php
@Method("my-bundle/my-method", context={"private"}, defaultContext=false) 
```

#### Yaml

Different endpoint implementation may utilize different controller name parsers, so
`MyBundle:Test:entity` notation is endpoint-dependent. I.e JSON-RPC may search `TestController` 
controller in `MyBundle\JsonRpc\TestController`

```yaml
my-bundle/my-method:
  controller: "MyBundle:Test:entity"
  default_context: true
  context: private
```

#### Resource

You can pass directory, class, file, yaml config as method source with 
prefix and context inheritance
 
The following chaing will result in `prefix/annotation/sub` method 
handled by `AnnotationController::subAction` with `private`+`default` context
 
```yaml
private:
  resource: jsonrpc_private_nested.yml
  context: private
```

```yaml
annotation:
  resource: "@JsonRpcTestBundle/JsonRpc"
  prefix: prefix/
```

```php
/**
 * Class AnnotationController
 *
 * @package Bankiru\Api\JsonRpc\Test\JsonRpc
 * @Method("annotation/")
 */
class AnnotationController extends Controller
{
    /**
     * @return array
     * @Method("sub")
     */
    public function subAction()
    {
        return [];
    }
}
```

## Events
 
This bundle repeats the generic symfony request processing flow. You can 
hook your extension into given system events

 * `rpc.request` is triggered on handling RPC call
    * Routing happens here
 * `rpc.controller` is triggered to filter controller (i.e. allows security filtering)
 * `rpc.response` is triggered whenever response is acquired by the endpoint processor 
 * `rpc.view` is triggered if response, returned from controller is not 
    instance of `RpcResponseInterface`
 * `rpc.exception` is triggered when exception is raised during RPC call processing
 * `rpc.finish_request` is used to finalize RPC response before it is returned to HTTP controller
