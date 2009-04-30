<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* News on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPDNewsGUI:
*
*/

class ilPDNewsGUI
{

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilPDNewsGUI()
	{
		global $tpl, $lng, $ilCtrl;

		// initiate variables
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->ctrl =& $ilCtrl;
		
		$lng->loadLanguageModule("news");
		
		$this->ctrl->saveParameter($this, "news_ref_id");
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();

		switch($next_class)
		{
				
			default:
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				break;
		}
		$this->tpl->show(true);
		return true;
	}

	/**
	* display header and locator
	*/
	function displayHeader()
	{
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"),
			$this->lng->txt("personal_desktop"));
		$this->tpl->setTitle($this->lng->txt("personal_desktop"));
		
		// display infopanel if something happened
		ilUtil::infoPanel();
	}

	/*
	* display notes
	*/
	function view()
	{
		global $ilUser, $lng, $tpl, $ilCtrl;

		$ref_ids = array();
		$obj_ids = array();
		$pd_items = $ilUser->getDesktopItems();
		foreach($pd_items as $item)
		{
			$ref_ids[] = $item["ref_id"];
			$obj_ids[] = $item["obj_id"];
		}
		
		$sel_ref_id = ($_GET["news_ref_id"] > 0)
			? $_GET["news_ref_id"]
			: $ilUser->getPref("news_sel_ref_id");
		
		include_once("./Services/News/classes/class.ilNewsItem.php");
		$per = ($_SESSION["news_pd_news_per"] != "")
			? $_SESSION["news_pd_news_per"]
			: ilNewsItem::_lookupUserPDPeriod($ilUser->getId());
		$news_obj_ids = ilNewsItem::filterObjIdsPerNews($obj_ids, $per);
		
		// related objects (contexts) of news
		$contexts[0] = $lng->txt("news_all_items");
		
		$conts = array();
		$sel_has_news = false;
		foreach ($ref_ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$title = ilObject::_lookupTitle($obj_id);
			
			$conts[$ref_id] = $title;
			if ($sel_ref_id == $ref_id)
			{
				$sel_has_news = true;
			}
		}
		
		$cnt = array();
		$nitem = new ilNewsItem();
		$news_items = $nitem->_getNewsItemsOfUser($ilUser->getId(), false,
			true, $per, $cnt);

		// reset selected news ref id, if no news are given for id
		if (!$sel_has_news)
		{
			$sel_ref_id = "";
		}
		asort($conts);
		foreach($conts as $ref_id => $title)
		{
			$contexts[$ref_id] = $title." (".(int) $cnt[$ref_id].")";
		}
		
		
		if ($sel_ref_id > 0)
		{
			$obj_id = ilObject::_lookupObjId($sel_ref_id);
			$obj_type = ilObject::_lookupType($obj_id);
			$nitem->setContextObjId($obj_id);
			$nitem->setContextObjType($obj_type);
			$news_items = $nitem->getNewsForRefId($sel_ref_id, false,
				false, $per, true);
		}
				
		include_once("./Services/News/classes/class.ilPDNewsTableGUI.php");
		$pd_news_table = new ilPDNewsTableGUI($this, "view", $contexts, $sel_ref_id);
		$pd_news_table->setData($news_items);
		$pd_news_table->setNoEntriesText($lng->txt("news_no_news_items"));
		
		$tpl->setContent($pd_news_table->getHTML());
	}
	
	/**
	* change related object
	*/
	function applyFilter()
	{
		global $ilUser;
		
		$this->ctrl->setParameter($this, "news_ref_id", $_POST["news_ref_id"]);
		$ilUser->writePref("news_sel_ref_id", $_POST["news_ref_id"]);
		if ($_POST["news_per"] > 0)
		{
			$_SESSION["news_pd_news_per"] = $_POST["news_per"];
		}
		$this->ctrl->redirect($this, "view");
	}

}
?>
