<?php

namespace Bankiru\Api\Rpc\Routing;

final class MatcherDumper
{
    /**
     * @param MethodCollection $collection
     * @param array            $options
     *
     * @return string
     */
    public function dump(MethodCollection $collection, array $options)
    {
        $content =
            <<<CONTENT
<?php

use Bankiru\Api\Rpc\Routing\Exception\MethodNotFoundException;
use Bankiru\Api\Rpc\Routing\MethodMatcher;

final class {$options['class']} implements MethodMatcher 
{
    /** {@inheritdoc} */
    public function match(\$method)
    {
        \$routes = [
CONTENT;

        foreach ($collection->all() as $name => $method) {
            $ret = var_export(AttributesHelper::getAttributes($method, $name), true);
            $content .= PHP_EOL .
                        <<<CONTENT
            '{$method->getMethod()}' => {$ret},
CONTENT;
        }

        $content .=
            <<<CONTENT
        ];
        
        if (array_key_exists(\$method, \$routes)) {
            return \$routes[\$method];
        }
        
        throw new MethodNotFoundException(\$method);
    }
}
CONTENT;

        return $content;
    }
}
