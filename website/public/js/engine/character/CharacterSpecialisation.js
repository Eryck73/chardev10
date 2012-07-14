/**
 * @constructor
 * @param {Array} serialized
 */
function CharacterSpecialisation( serialized ) {
	this.id = serialized[0];
	this.bg = serialized[1];
	this.spell = serialized[2] ? new Spell(serialized[2]) : null;
	this.name = serialized[3];
	this.description = serialized[4];
	this.icon = serialized[5];
}

CharacterSpecialisation.prototype = {
		id: 0,
		bg: "",
		spell: null,
		name: "",
		description: "",
		icon: ""
};