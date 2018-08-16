# 生成

## hsky:init

会生成 Service.php  ResultTrait.php  ServiceTrait.php

## hsky:service 
example : hsky:service DummyService
生成一个 service 文件

## hsky:trait
example : hsky:trait DummyTrait --service=DummyService
生成一个trait文件, 并且在DummyService中自动添加DummyTrait