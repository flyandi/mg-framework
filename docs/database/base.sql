SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `mg_analytics`
--

CREATE TABLE IF NOT EXISTS `mg_analytics` (
  `idstring` varchar(255) NOT NULL,
  `idrelated` varchar(128) DEFAULT NULL,
  `idstamp` int(11) NOT NULL DEFAULT '0',
  `idtype` int(11) NOT NULL DEFAULT '0',
  `source` varchar(1024) DEFAULT NULL,
  `meta` longtext,
  PRIMARY KEY (`idstring`),
  KEY `iduser` (`idrelated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_apiauth`
--

CREATE TABLE IF NOT EXISTS `mg_apiauth` (
  `idstring` varchar(255) NOT NULL,
  `apikey` varchar(128) DEFAULT NULL,
  `apisecret` varchar(128) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_assets`
--

CREATE TABLE IF NOT EXISTS `mg_assets` (
  `idstring` varchar(255) NOT NULL,
  `idrelated` varchar(128) DEFAULT NULL,
  `iduser` varchar(128) DEFAULT NULL,
  `idcustomer` varchar(32) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `source` longtext,
  `meta` text,
  `history` longtext,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_honeypotblocked`
--

CREATE TABLE IF NOT EXISTS `mg_honeypotblocked` (
  `idstring` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `idaddress` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `idtype` tinyint(1) NOT NULL DEFAULT '0',
  `created` bigint(20) NOT NULL DEFAULT '0',
  `timeout` bigint(20) NOT NULL DEFAULT '0',
  `reason` int(11) NOT NULL DEFAULT '0',
  `meta` text,
  PRIMARY KEY (`idstring`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_honeypottraffic`
--

CREATE TABLE IF NOT EXISTS `mg_honeypottraffic` (
  `idstring` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `idaddress` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `idtype` int(11) NOT NULL DEFAULT '0',
  `created` bigint(20) NOT NULL DEFAULT '0',
  `timeout` bigint(20) NOT NULL DEFAULT '0',
  `referer` text,
  `ua` varchar(512) DEFAULT NULL,
  `meta` text,
  PRIMARY KEY (`idstring`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_honeypottraps`
--

CREATE TABLE IF NOT EXISTS `mg_honeypottraps` (
  `idstring` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `idtype` int(11) NOT NULL DEFAULT '0',
  `idaddress` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `created` bigint(20) NOT NULL DEFAULT '0',
  `timeout` bigint(20) NOT NULL DEFAULT '0',
  `value` varchar(1024) DEFAULT NULL,
  `meta` text,
  PRIMARY KEY (`idstring`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_options`
--

CREATE TABLE IF NOT EXISTS `mg_options` (
  `idstring` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) DEFAULT NULL,
  `idtype` int(11) NOT NULL DEFAULT '0',
  `value` text,
  `mode` tinyint(1) NOT NULL DEFAULT '0',
  `secured` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mg_options`
--

INSERT INTO `mg_options` (`idstring`, `name`, `idtype`, `value`, `mode`, `secured`) VALUES
('171eb7f0776eda937f75298c13540ff8', 'requests.conditionnames', 4, 'a:5:{s:3:"any";s:3:"Any";s:3:"api";s:3:"API";s:4:"cron";s:4:"CRON";s:7:"secured";s:7:"Secured";s:9:"unsecured";s:9:"Unsecured";}', 1, 0),
('41e3a4ffcebb130d6644cc63d00fdf57', 'user.typenames', 4, 'a:7:{i:0;s:4:"None";i:1;s:6:"Member";i:2;s:4:"Lead";i:3;s:8:"Customer";i:4;s:5:"Sales";i:5;s:10:"Management";i:9999;s:14:"Administrators";}', 1, 0),
('55a3f2d76e0d278e4a0b3f686fc9dcdc', 'user.statusnames', 4, 'a:5:{i:0;s:4:"None";i:1;s:7:"Pending";i:2;s:4:"Sent";i:3;s:9:"Completed";i:5;s:9:"Preparing";}', 1, 0),
('698f17b79769155ae6662f4e69a51574', 'templates.typenames', 4, 'a:3:{s:4:"file";s:4:"File";s:4:"form";s:4:"Form";s:4:"page";s:4:"Page";}', 1, 0),
('822c62222014724e393b259fd5d69867', 'web.defaultmetafields', 4, 'a:5:{s:16:"content-language";s:10:"%LANGUAGE%";s:12:"content-type";s:29:"text/html; charset=iso-8859-1";s:11:"description";s:0:"";s:8:"keywords";s:0:"";s:5:"title";s:0:"";}', 1, 0),
('8eba8179579fe3b53aa5ca930463d5af', 'requests.typenames', 4, 'a:12:{s:11:"application";s:16:"Application Call";s:7:"content";s:7:"Content";s:9:"extension";s:9:"Extension";s:8:"http-301";s:24:"301 Redirect (Permanent)";s:8:"http-302";s:24:"302 Redirect (Temporary)";s:8:"http-303";s:20:"303 Redirect (Moved)";s:8:"http-403";s:13:"403 Forbidden";s:8:"http-404";s:13:"404 Not Found";s:9:"http-root";s:15:"Root (Redirect)";s:6:"logout";s:6:"Logout";s:7:"manager";s:13:"Manager Panel";s:7:"message";s:7:"Message";}', 1, 0),
('a91147e89b9331c133a715795a243739', 'region.states.us', 4, 'a:51:{s:2:"AL";s:7:"Alabama";s:2:"AK";s:6:"Alaska";s:2:"AZ";s:7:"Arizona";s:2:"AR";s:8:"Arkansas";s:2:"CA";s:10:"California";s:2:"CO";s:8:"Colorado";s:2:"CT";s:11:"Connecticut";s:2:"DE";s:8:"Delaware";s:2:"DC";s:20:"District Of Columbia";s:2:"FL";s:7:"Florida";s:2:"GA";s:7:"Georgia";s:2:"HI";s:6:"Hawaii";s:2:"ID";s:5:"Idaho";s:2:"IL";s:8:"Illinois";s:2:"IN";s:7:"Indiana";s:2:"IA";s:4:"Iowa";s:2:"KS";s:6:"Kansas";s:2:"KY";s:8:"Kentucky";s:2:"LA";s:9:"Louisiana";s:2:"ME";s:5:"Maine";s:2:"MD";s:8:"Maryland";s:2:"MA";s:13:"Massachusetts";s:2:"MI";s:8:"Michigan";s:2:"MN";s:9:"Minnesota";s:2:"MS";s:11:"Mississippi";s:2:"MO";s:8:"Missouri";s:2:"MT";s:7:"Montana";s:2:"NE";s:8:"Nebraska";s:2:"NV";s:6:"Nevada";s:2:"NH";s:13:"New Hampshire";s:2:"NJ";s:10:"New Jersey";s:2:"NM";s:10:"New Mexico";s:2:"NY";s:8:"New York";s:2:"NC";s:14:"North Carolina";s:2:"ND";s:12:"North Dakota";s:2:"OH";s:4:"Ohio";s:2:"OK";s:8:"Oklahoma";s:2:"OR";s:6:"Oregon";s:2:"PA";s:12:"Pennsylvania";s:2:"RI";s:12:"Rhode Island";s:2:"SC";s:14:"South Carolina";s:2:"SD";s:12:"South Dakota";s:2:"TN";s:9:"Tennessee";s:2:"TX";s:5:"Texas";s:2:"UT";s:4:"Utah";s:2:"VT";s:7:"Vermont";s:2:"VA";s:8:"Virginia";s:2:"WA";s:10:"Washington";s:2:"WV";s:13:"West Virginia";s:2:"WI";s:9:"Wisconsin";s:2:"WY";s:7:"Wyoming";}', 1, 1),
('ac270cc6c4a9f21117d7eb7c18f4f79d', 'templates.formatnames', 4, 'a:4:{s:4:"html";s:4:"HTML";s:4:"json";s:4:"JSON";s:4:"mson";s:4:"MSON";s:5:"plain";s:10:"Plain Text";}', 1, 0),
('e9f6d60dd7959c1bce031e474eac30ef', 'localization.languages', 4, 'a:3:{s:7:"english";s:7:"English";s:6:"german";s:7:"Deutsch";s:7:"spanish";s:7:"Spanish";}', 1, 0),
('f9c3ba248ae18502376e34bbdd447397', 'debug.parameters', 4, 'a:2:{s:13:"debug-enabled";s:4:"true";s:12:"debug-server";s:14:"210.912.902.10";}', 0, 0),
('fb004ccafef161df16aa94287ff69147', 'user.groupnames', 4, 'a:5:{i:0;s:3:"Any";i:1;s:4:"User";i:2;s:13:"Verified User";i:5000;s:7:"Manager";i:9999;s:13:"Administrator";}', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `mg_personality`
--

CREATE TABLE IF NOT EXISTS `mg_personality` (
  `idstring` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `description` varchar(128) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `domain` varchar(255) NOT NULL,
  `meta` longtext,
  `active` int(11) NOT NULL,
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mg_personality`
--

INSERT INTO `mg_personality` (`idstring`, `description`, `domain`, `meta`, `active`) VALUES
('default', 'default', 'default', 'a:6:{s:13:"configuration";a:12:{s:16:"apple-touch-icon";s:42:"/resources/images/mobile/icon-apple-%s.png";s:15:"cookie-lifetime";s:3:"180";s:16:"default-language";s:7:"english";s:12:"default-meta";a:12:{s:28:"apple-mobile-web-app-capable";s:3:"yes";s:8:"audience";s:3:"all";s:6:"author";s:17:"LawInfo.com, Inc.";s:16:"content-language";s:11:"{%language}";s:12:"content-type";s:29:"text/html; charset=iso-8859-1";s:11:"description";s:0:"";s:16:"handheldfriendly";s:4:"true";s:8:"keywords";s:0:"";s:9:"publisher";s:17:"LawInfo.com, Inc.";s:13:"revisit-after";s:5:"1 day";s:6:"robots";s:12:"INDEX,FOLLOW";s:8:"viewport";s:70:"width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no";}s:12:"email-signup";s:4:"true";s:15:"google-includes";s:4:"true";s:13:"html-compress";s:4:"true";s:22:"html-compress-bodyonly";s:4:"true";s:12:"http-nocache";s:4:"true";s:15:"manager-request";s:8:"/manager";s:11:"web-doctype";s:12:"DOCTYPE html";s:11:"web-favicon";s:29:"/resources/images/favicon.png";}s:9:"templates";a:4:{i:0;a:5:{s:4:"name";s:4:"blog";s:9:"reference";s:13:"TEMPLATE_BLOG";s:4:"type";s:4:"page";s:6:"format";s:4:"html";s:6:"source";a:1:{i:0;a:1:{s:6:"source";s:188:"<div class="-header manager">\n  <div class="-logo"><a href="/"></a></div>\n</div>\n<div class="-content -defaultfont">\n  <div>\n     <center>LawSmart Blog</center>\n  </div>\n  {CONTENT}\n</div>";}}}i:1;a:6:{s:4:"name";s:12:"indexnotitle";s:9:"reference";s:21:"TEMPLATE_INDEXNOTITLE";s:4:"type";s:4:"page";s:6:"format";s:4:"html";s:8:"location";s:0:"";s:6:"source";a:1:{i:0;a:1:{s:6:"source";s:163:"<div class="-header manager">\n  <div class="-logo"><a href="/"></a></div>\n</div>\n<div class="-content -defaultfont">\n  {&snippet:header.default}\n  {CONTENT}\n</div>";}}}i:2;a:6:{s:4:"name";s:7:"manager";s:9:"reference";s:16:"TEMPLATE_MANAGER";s:4:"type";s:4:"page";s:6:"format";s:4:"html";s:8:"location";s:0:"";s:6:"source";a:1:{i:0;a:1:{s:6:"source";s:522:"<div class="-topheader -defaultfont">\n  <div class="right">\n    <div class="-manager-info">\n      <div class="username">\n        Logged in as {$username}\n      </div>\n      <div class="links">\n        <a href="#" id="accountdetails">Account Settings</a>\n        <a href="/logout">Logout</a>\n      </div>\n      <div class="-clear"></div>\n	</div>\n  </div>\n</div>\n<div class="-content -defaultfont">\n  <div class="-main">\n    <div class="-inner">\n      <div class="-manager-container">\n      </div>\n    </div>\n  </div>\n</div>";}}}i:3;a:6:{s:4:"name";s:6:"signin";s:9:"reference";s:11:"FORM_SIGNIN";s:4:"type";s:4:"form";s:6:"format";s:4:"mson";s:8:"location";s:0:"";s:6:"source";a:1:{i:0;a:1:{s:6:"source";s:168:"signin_username: (none, text, SignInUsernameText, [required%SignInEmptyUsername]);\nsignin_password: (none, password, SignInPasswordText, [required%SignInEmptyPassword])";}}}}s:8:"requests";a:4:{i:0;a:6:{s:13:"localrequests";a:2:{s:7:"english";s:0:"";s:7:"spanish";s:0:"";}s:7:"request";s:4:"/app";s:4:"type";s:9:"extension";s:5:"value";s:3:"app";s:9:"condition";s:3:"any";s:7:"enabled";s:1:"1";}i:1;a:5:{s:13:"localrequests";a:3:{s:7:"english";s:0:"";s:6:"german";s:0:"";s:7:"spanish";s:0:"";}s:7:"request";s:5:"/blog";s:9:"condition";s:3:"any";s:4:"type";s:9:"extension";s:5:"value";s:4:"blog";}i:2;a:9:{s:13:"localrequests";a:2:{s:7:"english";s:0:"";s:7:"spanish";s:0:"";}s:7:"request";s:8:"/manager";s:9:"condition";s:7:"secured";s:4:"type";s:7:"manager";s:5:"value";s:0:"";s:7:"enabled";s:1:"1";s:8:"template";s:7:"manager";s:9:"usergroup";s:1:"1";s:8:"includes";s:26:"default,manager,screeninfo";}i:3;a:9:{s:13:"localrequests";a:2:{s:7:"english";s:0:"";s:7:"spanish";s:0:"";}s:7:"request";s:7:"/signin";s:9:"condition";s:9:"unsecured";s:4:"type";s:7:"content";s:5:"value";s:14:"signin.default";s:7:"enabled";s:1:"1";s:8:"includes";s:0:"";s:8:"template";s:5:"index";s:9:"usergroup";s:1:"0";}}s:8:"contents";a:1:{i:0;a:7:{s:9:"languages";a:3:{i:0;s:7:"default";i:1;s:7:"english";i:2;s:7:"spanish";}s:4:"name";s:14:"signin.default";s:9:"reference";s:0:"";s:8:"template";s:12:"indexnotitle";s:8:"location";s:0:"";s:6:"source";a:1:{s:7:"default";a:1:{s:6:"source";s:512:"<div class="-content -defaultfont">\n  <div class="-form -signin -corner-all">\n    <div class="-form-error -corner-all-small" style="display:{ACTIONERROR}">\n        {ACTIONERRORLIST}\n    </div>\n    <form method="post" id="signin" action="/signin">\n        {&form:signin}\n        <div class="buttons">\n            <input class="-button -corner-all-small" type="submit" value="{%SignInSubmitButton}" />\n        </div>\n      <input type="hidden" name="redirect" value="/manager" />\n      \n    </form>\n  </div>\n</div>";}}s:4:"meta";a:1:{s:7:"default";a:1:{s:8:"metadata";a:5:{s:16:"content-language";s:10:"%LANGUAGE%";s:12:"content-type";s:29:"text/html; charset=iso-8859-1";s:11:"description";s:0:"";s:8:"keywords";s:0:"";s:5:"title";s:0:"";}}}}}s:8:"snippets";a:3:{i:0;a:3:{s:9:"languages";a:4:{i:0;s:7:"default";i:1;s:7:"english";i:2;s:6:"german";i:3;s:7:"spanish";}s:4:"name";s:11:"commentform";s:6:"source";a:1:{s:7:"default";a:1:{s:6:"source";s:151:"<form method="/blog/{path}/comment">\n  <input name="firstname" />\n  <textarea name="comment"></textarea>\n  <button type="submit">Do It</button>\n</form>";}}}i:1;a:3:{s:9:"languages";a:3:{i:0;s:7:"default";i:1;s:7:"english";i:2;s:7:"spanish";}s:4:"name";s:6:"google";s:6:"source";a:1:{s:7:"default";a:1:{s:6:"source";s:94:"<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>";}}}i:2;a:3:{s:9:"languages";a:3:{i:0;s:7:"default";i:1;s:7:"english";i:2;s:7:"spanish";}s:4:"name";s:14:"header.default";s:6:"source";a:1:{s:7:"default";a:1:{s:6:"source";s:40:"<div>\n  This is my test header!!!\n</div>";}}}}s:6:"emails";s:0:"";}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `mg_providercache`
--

CREATE TABLE IF NOT EXISTS `mg_providercache` (
  `npa` varchar(4) NOT NULL DEFAULT '',
  `nxx` varchar(4) DEFAULT NULL,
  `pn` varchar(255) DEFAULT NULL,
  `pg` varchar(255) DEFAULT NULL,
  `up` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`npa`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_sessions`
--

CREATE TABLE IF NOT EXISTS `mg_sessions` (
  `idstring` varchar(255) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
  `idaddress` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
  `idstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `referer` text,
  `meta` text,
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_slugs`
--

CREATE TABLE IF NOT EXISTS `mg_slugs` (
  `idstring` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `stamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_statistics`
--

CREATE TABLE IF NOT EXISTS `mg_statistics` (
  `idstring` varchar(255) NOT NULL,
  `idstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `idaddress` varchar(64) DEFAULT NULL,
  `idsession` varchar(255) DEFAULT NULL,
  `iduser` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `code` int(11) NOT NULL DEFAULT '0',
  `meta` longtext,
  `request` text,
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mg_users`
--

CREATE TABLE IF NOT EXISTS `mg_users` (
  `idstring` varchar(255) NOT NULL,
  `idrelated` varchar(128) DEFAULT NULL,
  `idtype` int(11) NOT NULL DEFAULT '0',
  `idsession` varchar(255) DEFAULT NULL,
  `idaddress` varchar(255) DEFAULT NULL,
  `idtoken` varchar(128) DEFAULT NULL,
  `idcustomer` varchar(32) DEFAULT NULL,
  `idusername` varchar(255) DEFAULT NULL,
  `idpassword` varchar(255) DEFAULT NULL,
  `idlocalized` varchar(64) NOT NULL DEFAULT 'english',
  `meta` longtext,
  `joindate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` int(11) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `verify` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`idstring`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mg_users`
--

INSERT INTO `mg_users` (`idstring`, `idrelated`, `idtype`, `idsession`, `idaddress`, `idtoken`, `idcustomer`, `idusername`, `idpassword`, `idlocalized`, `meta`, `joindate`, `role`, `active`, `status`, `verify`) VALUES
('bd68cf4bd532bb260a29a523bde1889f', '', 9999, '', '', '', '1', 'admin', 'admin', 'english', '', '2012-02-02 21:19:19', 9999, 1, 0, 'confirmed');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
