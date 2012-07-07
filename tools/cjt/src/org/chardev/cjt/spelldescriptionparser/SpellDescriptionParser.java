package org.chardev.cjt.spelldescriptionparser;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Locale;

import org.chardev.cjt.entity.Spell;
import org.chardev.cjt.spelldescriptionparser.ParserStream.ParserException;
import org.chardev.cjt.spelldescriptionparser.ast.Expression;
import org.chardev.cjt.util.Database;

public class SpellDescriptionParser {
	
	protected Connection db;
	protected PreparedStatement updateStmt;
	
	public SpellDescriptionParser( Connection db ) {
		Locale.setDefault(Locale.ENGLISH);
		this.db = db;
		
		try {
			this.updateStmt = db.prepareStatement("UPDATE chardev_mop_static.`chardev_spellinfo` SET `DescriptionEN` = ? WHERE SpellID = ? ");
		} catch (SQLException e) {
			throw new RuntimeException(e);
		}
	}
	
	public void parseSpell( int spellId ) throws SQLException, ParserException {
		try {
			
			Spell s = new Spell(db, spellId);
				
			if( s.getDescription() == null ) {
				return;
			}
			
			Expression desc = new DescriptionParser(s.getDescription()).parse().evaluate(new Environment(db,s));
			
//			System.out.println(spellId + " >> " + new JsonPrinter().print(desc));
//			
//			if( desc instanceof Desc ) {
//				
//				for( Expression e : ((Desc)desc).getExps() ) {
//					System.err.println(e);
//				}
//			}
			
			updateStmt.setString(1, new JsonPrinter().print(desc));
			updateStmt.setInt(2, spellId);
			updateStmt.execute();
		}
		catch (Exception e) {
			System.out.println(">> " + spellId + ":" + e);
			e.printStackTrace();
		}	
	}
	
	public void parseDatabase() throws Throwable {
		
		ResultSet result = db.createStatement().executeQuery("SELECT `ID` FROM `Spell` LIMIT 0,1000000");
		
		int n = 0;
		while( result.next()) {
			final int id =result.getInt(1);
			parseSpell(id);
			if(n++%1000==0) System.out.println(id);
		}
	}
	
	public static void main(String[] args) throws Throwable {
		new SpellDescriptionParser(Database.connectToDatabase(Database.CHARDEV_MOP)).parseDatabase();
	}
}
