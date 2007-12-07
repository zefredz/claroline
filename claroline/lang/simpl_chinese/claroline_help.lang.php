<?php
$langAddIntro = "增加课程介绍";
$langAgenda = "计划安排";
$langAnnouncement = "公告通知";
$langDay_of_weekNames = "Array";
$langDelete = "删除";
$langDocContent = "<p>讲义材料 管理工具类似桌面电脑应用中的文件管理器.</p><p>您可以上传各种格式(HTML, Word, Powerpoint, Excel, Acrobat, Flash, Quicktime,等等)的文件,需要注意的是学生必须有相应的软件读取这些材料.而且,有些文件可能会携带病毒, 不要上传这样的文件也是您的责任.建议预先用杀毒软件检查后在上传.</p>
<p>文件以字母顺序排列.<br><b>提示 : </b>如果您需要以不同的顺序显示,在文件名中添加数字: 01, 02, 03...</p>
<p>您可以这么做 :</p>
<h4>上传文件</h4>
<ul>
  <li>选择您电脑上的文件,点击<input type=submit value=Browse name=submit2>
	在您屏幕的右侧.</li>
  <li>开始上传,点击上传按钮<input type=submit value=Upload name=submit2>
	.</li>
</ul>
<h4>为文件(或者目录)更名</h4>
<ul>
  <li>点击<img src=../document/img/edit.gif width=20 height=20 align=baseline> 按钮(位于'更名'栏)</li>
  <li>在左上角区域填写新的文件名</li>
  <li>确认,点击<input type=submit value=Ok name=submit24>
	. 
</ul>
	<h4>删除文件(或目录)</h4>
	<ul>
	  
  <li>点击 <img src=../document/img/delete.gif width=20 height=20> 
	(位于 '删除'栏).</li>
	</ul>
	<h4>显示/隐藏文件(或目录)</h4>
	<ul>
	  
  <li>点击 <img src=../document/img/visible.gif width=20 height=20>位于'显示/隐藏'栏.</li>
	  <li>隐藏文件(或目录)后,它还是存在的,只是对学生已经不可见.</li>	  
  <li>要使得其对学生可见, 点击 <img src=../img/invisible.gif width=24 height=20> 
	(位于'显示/隐藏'栏)</li>
	</ul>
	<h4>为文件(或目录)添加或更新摘要</h4>
	<ul>	  
  <li>点击 <img src=../img/comment.gif width=20 height=20> 
	(位于'摘要'栏)</li>
	  <li>在右上角相应区域填写新的摘要.</li>
	  <li>确认,点击<input type=submit value=OK name=submit2>
		.</li>
	</ul> 
	<p>删除摘要,点击 <img src=../img/comment.gif width=20 height=20>, 
	  删除相应区域旧的摘要 <input type=submit value=OK name=submit22>
	  . 
	<hr>
	<p>通过 分类 整理来组织内容. 如下所示:</p>
	<h4><b>创建一个目录</b></h4>
	<ul>
	  <li>点击 <img src=../img/folder.gif> '创建目录'(左上角)</li>
	  <li>在相应区域键入新目录的名称(左上角)</li>
	  <li>确认,点击 <input type=submit value=OK name=submit23>.</li>
	</ul>
	<h4>移动文件(或目录)</h4>
	<ul>
	  <li>点击 <img src=../img/deplacer.gif width=34 height=16> 
		位于'移动'栏</li>
	  <li>在相应下拉菜单(左上)选择目标文件(或目录).(提示: 'root' 意思是已经位于文件目录树的顶层了,服务器上的相应上层目录不能上传文件.</li>
	  <li>确认,点击 <input type=submit value=OK name=submit232>.</li>
	</ul>
	<center>
	  <p>";
$langDocument = "讲义材料";
$langForContent = "论坛,是异步讨论工具.电子邮件使用一对一的对话方式,论坛则使得公共对话或者半公共的对话成为可能.</p><p>从技术上讲,使用claroline论坛只需要使用浏览器就可以.</P><p>组织论坛,请点击'管理'.论坛按照类和子类组织起来,如下:</p><p><b>分类 > 论坛 > 论题 > 回复 </b></p>为使学生的讨论有一个清晰的结构,预先组织分类和论坛是必要的.让学生来张贴话题和回复.默认设置,claroline论坛只包含'Public'分类 -- 示例性的论坛和话题.</p><p>所以首先要做的事,删除话题示例,更新第一个论坛的名称.接下来,可以在'public'分类中按照小组或主题创建新的论坛来满足课程学习环境的需要.</p><p>不要混淆分类和论坛, 也不要忘记空的类别(不含有论坛)对学生是不可见的.</p><p>论坛的简介可以是它的用户列表,目标,任务,主题等等";
$langForums = "学习论坛";
$langHClar = "上手帮助";
$langHDoc = "讲义帮助";
$langHFor = "论坛帮助";
$langHHome = "网站帮助";
$langHUser = "用户列表 帮助";
$langHelp = "帮助";
$langHomeContent = "为方便掌握使用方法,所有claroline工具都不是空的.每个工具都有一个简短的例子帮助您快速掌握它的使用方法.更新还是删除这个例子取决于你.</p><p>举例来说, 在您的课程主页上有一段文字,'这是您的课程简介.要替换为您自己的文字,点击下面'更新'. 就是这么简单!! 并且每个工具都有相同相似的逻辑功能: 增加, 删除, 更新, 这些功能正是动态网页的功能.</p><p>当您新建课程网站时, 大部分工具是激活的. 请记住, 使那些不需要的功能隐藏起来取决于您,只需点击'隐藏'即可实现. 接下来,解释一下主页上灰色部分的功能. 这部分功能对您的学生是不可见的, 当然您可以在任何时候激活它们.</p><p>您可以添加您自己的网页. 当然这些网页必需是HTML格式的(网页可以用Word处理器或者Web编辑器生成). 使用 '上传网页'来实现. 标准的文件头会自动与您的文件融和在一起,您只需要把精力放在文件内容上就行了. 如果您需要在主页上添加到Web上已存在网页的链接,(当然也可以是在主页内的文件), 请使用 '添加链接', 您自己添加的网页可以隐藏甚至删除,但标准工具只能隐藏,不能删除.</p><p>课程网站制作完毕后,请到'课程信息',指定您的网站访问限制策略. 缺省设置,您的课程是未开放的,(因为默认假设您的网站正在制作之中).</p>";
$langLogout = "退出系统";
$langManager = "管理员";
$langModify = "更新";
$langModifyProfile = "个人资料";
$langMonthNames = "Array";
$langMyCourses = "我的课程";
$langNotAllowed = "操作禁止";
$langOk = "确认";
$langPoweredBy = "技术支持";
$langReg = "注册";
$langStudent = "学生";
$langUserContent = "<b>角色</b><p>角色没有计算机管理相关的功能. 这并不赋予谁系统操作的权力. 这只是告诉我们,每个人在教学过程中扮演的角色.您可以点击'角色'下面的'更新'来更新, 可以键入任何您认为的角色:教授, 助教, 学生, 过客, 专家...</P><hr>
<b>管理权力</b>
<p>管理权利,另一方面,对应于系统操作权力,更新内容,组织课程网站等. 目前为之, 您只能选择赋予所有权力,或者相反,不赋予任何权力.</P>
<p>举例来说,使一位助教来共同管理网站,您需要确保他已在本课程注册,点击 '管理权力'下的'更新',然后选择'所有', '确认'即可.</P><hr>
<b>联合主讲</b>
<p>为在课程网站显示联合主讲的名字,请使用'更新课程信息'工具(桔黄色). 这项更新并不为联合主讲在本课程注册.'教授'域和用户列表是相互独立的.</p><hr>
<b>增加用户</b>
<p>为本课程增加一个用户,首先检查该用户是否已在虚拟校园注册.如果已经注册, 选择他名字旁边的选项格,然后'确认'. 如果还没有注册, 可以手工添加. 任何一种情况, 他都会收到一封含用户名和密码的确认邮件.</p>";
$langUserName = "用户名";
$langUsers = "用户列表";
$langWork = "作业论文";
?>