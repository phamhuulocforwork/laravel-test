<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Product Management API",
 *     version="1.0.0",
 *     description="RESTful API for Product Management with JWT Authentication",
 *     @OA\Contact(
 *         email=""
 *     )
 * )
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter JWT token in format: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
