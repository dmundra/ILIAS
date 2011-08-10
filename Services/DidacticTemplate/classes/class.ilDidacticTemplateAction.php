<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract class for template actions
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
abstract class ilDidacticTemplateAction
{
	const FILTER_POSITIVE = 1;
	const FILTER_NEGATIVE = 2;

	const TYPE_LOCAL_POLICY = 1;
	const TYPE_LOCAL_ROLE = 2;


	private $action_id = 0;
	private $tpl_id = 0;
	private $ref_id = 0;


	/**
	 * Constructor
	 */
	public function __construct($action_id = 0)
	{
		$this->read();
	}

	/**
	 * Get action id
	 * @return int
	 */
	public function getActionId()
	{
		return $this->action_id;
	}

	/**
	 * Set action id
	 * @param int $a_action_id
	 */
	public function setActionId($a_action_id)
	{
		$this->action_id = $a_action_id;
	}

	/**
	 * Set reference id of current object
	 *
	 * @param int ref id
	 */
	public function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}

	/**
	 * Get ref id of current object
	 * @return <type>
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}

	/**
	 * Set template id
	 * @param int $a_id
	 */
	public function setTemplateId($a_id)
	{
		$this->tpl_id = $a_id;
	}

	/**
	 * Get template id
	 * @return int
	 */
	public function getTemplateId()
	{
		return $this->tpl_id;
	}

	/**
	 * write action to db
	 * overwrite for filling additional db fields
	 *
	 * @return bool
	 */
	public function save()
	{
		global $ilDB;

		if($this->getActionId())
		{
			return false;
		}

		$this->setActionId($ilDB->nextId('didactic_tpl_a'));
		$query = 'INSERT INTO didactic_tpl_a (id, tpl_id, type_id) '.
			'VALUES( '.
			$ilDB->quote($this->getActionId(),'integer').', '.
			$ilDB->quote($this->getTemplateId(),'integer').', '.
			$ilDB->quote($this->getType(),'integer').
			')';
		$ilDB->manipulate($query);
		return $this->getActionId();
	}

	/**
	 * Delete didactic template action
	 * overwrite for filling additional db fields
	 *
	 * @return bool
	 */
	public function delete()
	{
		global $ilDB;

		$query = 'DELETE FROM didactic_tpl_a '.
			'WHERE id = '.$ilDB->quote($this->getActionId(),'integer');
		$ilDB->manipulate($query);
	}

	/**
	 *
	 * @global ilDB $ilDB
	 */
	public function read()
	{
		global $ilDB;

		$query = 'SELECT * FROM didactic_tpl_a '.
			'WHERE id = '.$ilDB->quote($this->getActionId(), 'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->setTemplateId($row->tpl_id);
		}
	}

	/**
	 * Get type of template
	 * @return int $type
	 */
	abstract public function getType();

	/**
	 * Apply action
	 *
	 * @return bool
	 */
	abstract public function apply();

	/**
	 * Implement everthing that is necessary to revert a didactic template
	 *
	 * return bool
	 */
	abstract public function revert();


	/**
	 * Clone method
	 */
	public function __clone()
	{
		$this->setActionId(0);
	}


	/**
	 * Write xml for export
	 */
	abstract function toXml(ilXmlWriter $writer);

}
?>
