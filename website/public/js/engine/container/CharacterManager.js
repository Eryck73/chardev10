/**
 * @constructor
 */
function CharacterManager( ) {
	this.characters = [];
	this.eventMgr = new GenericSubject();
	this.eventMgr.registerEvent('change',['character']);
	this.eventMgr.registerEvent('add',['character']);
	this.eventMgr.registerEvent('remove',['character']);
}

CharacterManager.prototype = {
	/** @type {Array} */
	characters: null,
	/** @type {GenericSubject} */
	eventMgr: null,
	/** @type {number} */
	cursor: -1,
	/**
	 * Adds, selects and returns a new character
	 * 
	 * @param {Character} character
	 */
	add: function() {
		var character = new Character();
		
		this.characters.push( character );
		this.cursor = this.characters.length - 1;

		this.eventMgr.fire( 'add', {'character': character});
		this.eventMgr.fire( 'change', {'character': character} );
		
		return character;
	},
	/**
	 * Selects the character at specified index
	 * 
	 * @param {number} index
	 */
	select: function( index ) {
		if( index >= this.characters.length || index < 0 ) {
			throw new Error("Invalid index: " + index);
		}
		
		this.cursor = index;
		this.eventMgr.fire( 'change', {'character': this.getCurrent()} );
	},
	/**
	 * Removes the character at given index
	 * 
	 * @param index
	 */
	removeIndex: function( index ) {
		if( index >= this.characters.length || index < 0 ) {
			throw new Error("Invalid index: " + index);
		}
		this.remove( this.characters[index]);
	},
	/**
	 * Removes given character
	 * 
	 * @param {Character} character
	 */
	remove: function( character ) {
		if( character == null ) {
			throw new Exception("Character may not be null!");
		}
		
		for( var k in this.characters ) {
			if( this.characters[k] != character ) {
				continue;
			}

			this.characters.splice( k , 1 );
			
			if( k == this.cursor ) {
				if( this.characters.length == 0 ) {
					this.cursor = -1;
				}
				else {
					if( this.cursor > 0 ) {
						this.cursor -- ;
					}
//					else {
//						this.cursor;
//					}
				}
				
				this.eventMgr.fire( 'change', {'character': this.getCurrent()} );
			}
			
			this.eventMgr.fire( 'remove', {'character': character});
			
			return;
		}
	},
	/**
	 * Returns the currently selected character
	 * 
	 * @returns {Character} Current character
	 */
	getCurrent: function() {
		if( this.cursor == -1 ) {
			return null;
		}
		return this.characters[ this.cursor ];
	},
	/**
	 * Returns true if the given index is selected
	 */
	isSelected: function( index ) {
		return this.cursor == index;
	},
	/**
	 * Returns the character at given index
	 */
	get: function( index ) {
		return this.characters[index];
	},
	/**
	 * @param {GenericObserver} observer
	 */
	addObserver: function( observer ) {
		this.eventMgr.addObserver(observer);
	},
	/**
	 * @param {GenericSubject} propagator
	 */
	addPropagator: function(propagator) {
		this.eventMgr.addPropagator(propagator);
	}
};