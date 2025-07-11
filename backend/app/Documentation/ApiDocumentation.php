<?php

/**
 * @OA\Info(
 *     title="Iron Code Skins API",
 *     version="1.0.0",
 *     description="API para plataforma de escrow jurídico de skins CS:GO",
 *     @OA\Contact(
 *         email="tech@ironcodeskins.com"
 *     ),
 *     @OA\License(
 *         name="Proprietary",
 *         url="https://ironcodeskins.com/license"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost/api",
 *     description="Development Server"
 * )
 * 
 * @OA\Server(
 *     url="https://staging.ironcodeskins.com/api",
 *     description="Staging Server"
 * )
 * 
 * @OA\Server(
 *     url="https://api.ironcodeskins.com",
 *     description="Production Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Health",
 *     description="Health check endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization"
 * )
 * 
 * @OA\Tag(
 *     name="Users",
 *     description="User management"
 * )
 * 
 * @OA\Tag(
 *     name="Transactions",
 *     description="Escrow transactions"
 * )
 * 
 * @OA\Tag(
 *     name="Skins",
 *     description="CS:GO skins management"
 * )
 */
