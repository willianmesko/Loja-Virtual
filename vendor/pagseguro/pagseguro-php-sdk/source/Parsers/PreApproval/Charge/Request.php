<?php
/**
 * 2007-2016 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    PagSeguro Internet Ltda.
 * @copyright 2007-2016 PagSeguro Internet Ltda.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 *
 */

namespace PagSeguro\Parsers\PreApproval\Charge;

use PagSeguro\Domains\Requests\Requests;
use PagSeguro\Enum\Properties\Current;
use PagSeguro\Parsers\Basic;
use PagSeguro\Parsers\Item;
use PagSeguro\Parsers\Error;
use PagSeguro\Parsers\Parser;
use PagSeguro\Parsers\PreApproval\Response;
use PagSeguro\Resources\Http;

/**
 * Request class
 */
class Request extends Error implements Parser
{
    use Basic;
    use Item;

    /**
     * @param Requests $request
     * @param $request
     * @return array
     */
    public static function getData(Requests $request)
    {
        $data = [];
        $properties = new Current;
        if (!is_null($request->getCode())) {
                $data[$properties::PRE_APPROVAL_CODE] = $request->getCode();
        }
        return array_merge(
            $data,
            Basic::getData($request, $properties),
            Item::getData($request, $properties)
        );
    }

    /**
     * @param \PagSeguro\Resources\Http $http
     * @return Response
     */
    public static function success(Http $http)
    {
        $xml = simplexml_load_string($http->getResponse());
        return (new Response)->setCode(current($xml->transactionCode))
                             ->setDate(current($xml->date));
    }
    
    /**
     * @param \PagSeguro\Resources\Http $http
     * @return \PagSeguro\Domains\Error
     */
    public static function error(Http $http)
    {
        $error = parent::error($http);
        return $error;
    }
}
