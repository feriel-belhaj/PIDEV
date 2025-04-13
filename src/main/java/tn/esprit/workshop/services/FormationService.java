package tn.esprit.workshop.services;

import tn.esprit.workshop.entities.Formation;
import tn.esprit.workshop.utils.MyDbConnexion;

import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class FormationService {
    private Connection connection;

    public FormationService() {
        connection = MyDbConnexion.getInstance().getCnx();
    }

    public void ajouter(Formation formation) throws SQLException {
        String query = "INSERT INTO formation (titre, description, datedeb, datefin, niveau, prix, emplacement, nbplace, nbparticipant, organisateur, duree, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        try (PreparedStatement ps = connection.prepareStatement(query, Statement.RETURN_GENERATED_KEYS)) {
            ps.setString(1, formation.getTitre());
            ps.setString(2, formation.getDescription());
            ps.setDate(3, Date.valueOf(formation.getDateDeb()));
            ps.setDate(4, Date.valueOf(formation.getDateFin()));
            ps.setString(5, formation.getNiveau());
            ps.setFloat(6, formation.getPrix());
            ps.setString(7, formation.getEmplacement());
            ps.setInt(8, formation.getNbPlace());
            ps.setInt(9, formation.getNbParticipant());
            ps.setString(10, formation.getOrganisateur());
            ps.setString(11, formation.getDuree());
            ps.setString(12, formation.getImage());
            ps.executeUpdate();

            ResultSet rs = ps.getGeneratedKeys();
            if (rs.next()) {
                formation.setId(rs.getInt(1));
            }
        }
    }

    public void modifier(Formation formation) throws SQLException {
        String query = "UPDATE formation SET titre=?, description=?, datedeb=?, datefin=?, niveau=?, prix=?, emplacement=?, nbplace=?, nbparticipant=?, organisateur=?, duree=?, image=? WHERE id=?";
        try (PreparedStatement ps = connection.prepareStatement(query)) {
            ps.setString(1, formation.getTitre());
            ps.setString(2, formation.getDescription());
            ps.setDate(3, Date.valueOf(formation.getDateDeb()));
            ps.setDate(4, Date.valueOf(formation.getDateFin()));
            ps.setString(5, formation.getNiveau());
            ps.setFloat(6, formation.getPrix());
            ps.setString(7, formation.getEmplacement());
            ps.setInt(8, formation.getNbPlace());
            ps.setInt(9, formation.getNbParticipant());
            ps.setString(10, formation.getOrganisateur());
            ps.setString(11, formation.getDuree());
            ps.setString(12, formation.getImage());
            ps.setInt(13, formation.getId());
            ps.executeUpdate();
        }
    }

    public void supprimer(int id) throws SQLException {
        String query = "DELETE FROM formation WHERE id=?";
        try (PreparedStatement ps = connection.prepareStatement(query)) {
            ps.setInt(1, id);
            ps.executeUpdate();
        }
    }

    public Formation getById(int id) throws SQLException {
        String query = "SELECT * FROM formation WHERE id=?";
        try (PreparedStatement ps = connection.prepareStatement(query)) {
            ps.setInt(1, id);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                return extractFormationFromResultSet(rs);
            }
        }
        return null;
    }

    public List<Formation> getAll() throws SQLException {
        List<Formation> formations = new ArrayList<>();
        String query = "SELECT * FROM formation";
        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(query)) {
            while (rs.next()) {
                formations.add(extractFormationFromResultSet(rs));
            }
        }
        return formations;
    }

    private Formation extractFormationFromResultSet(ResultSet rs) throws SQLException {
        Formation formation = new Formation();
        formation.setId(rs.getInt("id"));
        formation.setTitre(rs.getString("titre"));
        formation.setDescription(rs.getString("description"));
        formation.setDateDeb(rs.getDate("datedeb").toLocalDate());
        formation.setDateFin(rs.getDate("datefin").toLocalDate());
        formation.setNiveau(rs.getString("niveau"));
        formation.setPrix(rs.getFloat("prix"));
        formation.setEmplacement(rs.getString("emplacement"));
        formation.setNbPlace(rs.getInt("nbplace"));
        formation.setNbParticipant(rs.getInt("nbparticipant"));
        formation.setOrganisateur(rs.getString("organisateur"));
        formation.setDuree(rs.getString("duree"));
        formation.setImage(rs.getString("image"));
        return formation;
    }
} 