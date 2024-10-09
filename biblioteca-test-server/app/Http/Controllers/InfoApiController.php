<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *      title="ThreeLeaf.com Biblioteca API",
 *      description="APIs for the Biblioteca Models.",
 *      version="1.0.0",
 *      @OA\Contact(
 *          email="biblioteca@threeleafcom.com"
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
