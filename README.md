##daemon io system

Usage:
------
`launcher.php [Command] [ClassName] [Options] [Arguments]`

Version:
------
0.3 ALPHA

约定
------
* app下一级目录为控制类
* 二级以上目录为模型类
* 文件夹全用小写
* 大写字母开头为类文件
* 小写字母开头为include文件（包括方法集合，配置文件）
* 方法使用驼峰命名

文件目录
------
* sys/          框架文件
* sys/lib/      引入api文件，不在代码中调用
* config/       配置文件
* app/          项目文件
* var/log/      log文件
* var/man/      帮助文件

NOTICE
---
* Redis配置的timeout需要设置为0
