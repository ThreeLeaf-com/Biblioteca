<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="ThreeLeaf.com Biblioteca APIs",
 *      description="APIs for the Bibliotecha Models.",
 *      @OA\Contact(
 *          email="api@threeleafcom.com"
 *      )
 * )
 * @OA\OpenApi(
 *     @OA\Server(
 *         url="https://local.threeleaf.com",
 *         description="Local Development Environment"
 *     ),
 *     @OA\Server(
 *         url="https://staging.threeleaf.com",
 *         description="Staging Environment"
 *     ),
 *     @OA\Server(
 *         url="https://threeleaf.com",
 *         description="Production Environment"
 *     ),
 * )
 */
class InfoApiController
{

}
