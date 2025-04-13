package tn.esprit.workshop.gui;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.fxml.Initializable;
import javafx.scene.Parent;
import javafx.scene.Node;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Label;
import javafx.scene.image.Image;
import javafx.scene.image.ImageView;
import javafx.scene.layout.FlowPane;
import javafx.scene.layout.StackPane;
import javafx.scene.layout.VBox;
import tn.esprit.workshop.entities.Formation;
import tn.esprit.workshop.services.FormationService;

import java.io.IOException;
import java.net.URL;
import java.sql.SQLException;
import java.util.ResourceBundle;

public class ClientController implements Initializable {
    @FXML private FlowPane formationsContainer;
    @FXML private StackPane contentArea;
    private FormationService formationService;

    @Override
    public void initialize(URL url, ResourceBundle rb) {
        formationService = new FormationService();
        loadFormations();
    }

    private void loadFormations() {
        try {
            formationsContainer.getChildren().clear();
            for (Formation formation : formationService.getAll()) {
                formationsContainer.getChildren().add(createFormationCard(formation));
            }
        } catch (SQLException e) {
            showAlert(Alert.AlertType.ERROR, "Erreur", "Erreur lors du chargement des formations: " + e.getMessage());
        }
    }

    private VBox createFormationCard(Formation formation) {
        VBox card = new VBox(10);
        card.getStyleClass().add("formation-card");
        card.setPrefWidth(250);
        card.setPrefHeight(350);

        // Conteneur pour l'image
        StackPane imageContainer = new StackPane();
        imageContainer.setPrefWidth(250);
        imageContainer.setPrefHeight(150);
        imageContainer.setStyle("-fx-background-color: #e0e0e0;");

        // Image de la formation
        ImageView imageView = new ImageView();
        imageView.setFitWidth(250);
        imageView.setFitHeight(150);
        imageView.setPreserveRatio(false);
        
        try {
            if (formation.getImage() != null && !formation.getImage().isEmpty()) {
                URL imageUrl = getClass().getResource("/" + formation.getImage());
                if (imageUrl != null) {
                    imageView.setImage(new Image(imageUrl.toExternalForm()));
                    imageContainer.getChildren().add(imageView);
                } else {
                    // Si l'image n'est pas trouvée, afficher un message
                    Label noImageLabel = new Label("Pas d'image");
                    noImageLabel.setStyle("-fx-text-fill: #666666;");
                    imageContainer.getChildren().add(noImageLabel);
                }
            } else {
                // Si aucune image n'est spécifiée
                Label noImageLabel = new Label("Pas d'image");
                noImageLabel.setStyle("-fx-text-fill: #666666;");
                imageContainer.getChildren().add(noImageLabel);
            }
        } catch (Exception e) {
            // En cas d'erreur, afficher un message
            Label noImageLabel = new Label("Pas d'image");
            noImageLabel.setStyle("-fx-text-fill: #666666;");
            imageContainer.getChildren().add(noImageLabel);
        }

        // Titre de la formation
        Label titleLabel = new Label(formation.getTitre());
        titleLabel.getStyleClass().add("formation-title");
        titleLabel.setWrapText(true);

        // Description courte
        Label descriptionLabel = new Label(formation.getDescription());
        descriptionLabel.getStyleClass().add("formation-description");
        descriptionLabel.setWrapText(true);
        descriptionLabel.setMaxHeight(60);

        // Niveau
        Label levelLabel = new Label("Niveau: " + formation.getNiveau());
        levelLabel.getStyleClass().add("formation-level");

        // Prix
        Label priceLabel = new Label("Prix: " + formation.getPrix() + " DT");
        priceLabel.getStyleClass().add("formation-price");

        // Bouton "Voir plus"
        Button detailsButton = new Button("Voir plus");
        detailsButton.getStyleClass().add("details-button");
        detailsButton.setOnAction(e -> showFormationDetails(formation));

        card.getChildren().addAll(imageContainer, titleLabel, descriptionLabel, levelLabel, priceLabel, detailsButton);
        return card;
    }

    @FXML
    private void showFormationDetails(Formation formation) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/fxml/FormationDetailsView.fxml"));
            Parent detailsView = loader.load();
            
            FormationDetailsController controller = loader.getController();
            controller.setFormation(formation);
            
            // Sauvegarder la vue actuelle
            Node currentContent = contentArea.getChildren().get(0);
            if (currentContent instanceof Parent) {
                controller.setPreviousView((Parent) currentContent);
            }
            
            contentArea.getChildren().clear();
            contentArea.getChildren().add(detailsView);
        } catch (IOException e) {
            e.printStackTrace();
            showAlert(Alert.AlertType.ERROR, "Erreur", "Impossible d'afficher les détails de la formation.");
        }
    }

    private void showAlert(Alert.AlertType type, String title, String content) {
        Alert alert = new Alert(type);
        alert.setTitle(title);
        alert.setContentText(content);
        alert.showAndWait();
    }
} 