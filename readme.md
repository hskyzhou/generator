# 生成

## hsky:controller -h 查看帮助
## hsky:controller Backend/User/Login
1. 会自动在app/Http/Controllers/Backend/User下面创建LoginController
2. 会自动在app/Http/Requests/Backend/User下面创建LoginRequest
3. 会自动在app/Services下面创建UserService
4. 会咨询创建entity的name，会生成对应的model，repository, interface
5. 会自动在app/Exceptions/User下面创建Create, Update, Delete的exception
6. 如果ErrorCode如果不存在会自动创建

## hsky:service User
1. 会自动在app/Services下面创建UserService
2. 会咨询创建entity的name，会生成对应的model，repository, interface
3. 会自动在app/Exceptions/User下面创建Create, Update, Delete的exception
4. 如果ErrorCode如果不存在会自动创建

## hsky:exception User/Create
1. 会自动在app/Exceptions/User下面创建CreateException
2. 如果ErrorCode如果不存在会自动创建