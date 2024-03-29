DROP TABLE Affecte;
DROP TABLE Cartes;
DROP TABLE Colonnes;
DROP TABLE Participe;
DROP TABLE Tableaux;
DROP TABLE Utilisateurs;

CREATE TABLE Utilisateurs(
    login VARCHAR(30),
    nom VARCHAR(30),
    prenom VARCHAR(30),
    email VARCHAR(255),
    mdpHache VARCHAR(255),
    PRIMARY KEY (login)
);

CREATE TABLE Tableaux(
    idTableau SERIAL PRIMARY KEY,
    login VARCHAR(30) NOT NULL,
    codeTableau VARCHAR(255),
    titreTableau VARCHAR (50),
    FOREIGN KEY (login) REFERENCES Utilisateurs
);

CREATE TABLE Participe(
    idTableau INT,
    login VARCHAR(30),
    PRIMARY KEY (idTableau, login),
    FOREIGN KEY (idTableau) REFERENCES Tableaux,
    FOREIGN KEY (login) REFERENCES Utilisateurs
);

CREATE TABLE Colonnes(
    idColonne SERIAL PRIMARY KEY,
    titreColonne VARCHAR(50),
    idTableau INT NOT NULL,
    FOREIGN KEY (idTableau) REFERENCES Tableaux
);

CREATE TABLE Cartes(
    idCarte SERIAL PRIMARY KEY,
    titreCarte VARCHAR(50),
    descriptifCarte TEXT,
    couleurCarte VARCHAR(7),
    idColonne INT NOT NULL,
    FOREIGN KEY (idColonne) REFERENCES Colonnes
);

CREATE TABLE Affecte(
    idCarte INT,
    login VARCHAR(30),
    PRIMARY KEY (idCarte, login),
    FOREIGN KEY (idCarte) REFERENCES Cartes,
    FOREIGN KEY (login) REFERENCES Utilisateurs
)