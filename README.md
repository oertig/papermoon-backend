# papermoon-backend

A RESTful API written in PHP to provide data on FGO banners.\
The API only accepts GET requests.\
All requests are routed via a `.htaccess` file to the index.php file of the API.\
The API is separated into versions, so for version one of the API you would call `/api/v1/{resourceYouDesire}?{parametersYouDesire}`.

## possible resources and their parameters

### banners

-   id: comma-separated
-   status: active, inactive, expired, upcoming
-   servant: by id, comma-separated
-   start_date, end_date:
    -   in the format YYYY-MM-DD
    -   with possible specifiers:
        -   gte: greater than or equal
        -   lte: less than or equal
        -   gt: greater than
        -   lt: less than
        -   eq: equal

### example requests fo banners resource

-   /api/v1/banners
-   /api/v1/banners?id=1,2,3
-   /api/v1/banners?status=active
-   /api/v1/banners?servant=1,2,3
-   /api/v1/banners?start_date=2024-01-01&end_date=2024-12-31 // no specifiers given, defaults to [eq]
-   /api/v1/banners?id=1&servant=1,2,3&status=active&start_date[gte]=2024-01-03&end_date[lte]=2024-01-07

### servants

-   to be added

### example requests for servants resource

-   to be added

## future features

-   to be added
