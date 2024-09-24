<?php
/**
 * The VrijBRPToZGWBundle
 *
 * @author  Conduction.nl <info@conduction.nl>
 * @license EUPL-1.2 https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace CommonGateway\VrijBRPToZGWBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class VrijBRPToZGWBundle extends Bundle
{


    /**
     * Returns the path the bundle is in
     *
     * @return string
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);

    }//end getPath()


}//end class
