
DROP TABLE Affecte;
DROP TABLE Cartes;
DROP TABLE Colonnes;
DROP TABLE Participe;
DROP TABLE Tableaux;
DROP TABLE Utilisateurs;

CREATE TABLE Utilisateurs(
    login VARCHAR(100) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    mdpHache VARCHAR(100) NOT NULL,
    PRIMARY KEY (login)
);

CREATE TABLE Tableaux(
    idTableau INT NOT NULL,
    login VARCHAR(100) NOT NULL,
    codeTableau VARCHAR(255),
    titreTableau VARCHAR (100),
    PRIMARY KEY (idTableau),
    FOREIGN KEY (login) REFERENCES Utilisateurs
);

CREATE TABLE Participe(
    idTableau INT NOT NULL,
    login VARCHAR(100),
    PRIMARY KEY (idTableau, login),
    FOREIGN KEY (idTableau) REFERENCES Tableaux,
    FOREIGN KEY (login) REFERENCES Utilisateurs
);

CREATE TABLE Colonnes(
    idColonne INT NOT NULL,
    titreColonne VARCHAR(100) NOT NULL,
    idTableau INT NOT NULL,
    PRIMARY KEY (idColonne),
    FOREIGN KEY (idTableau) REFERENCES Tableaux
);

CREATE TABLE Cartes(
    idCarte INT NOT NULL,
    titreCarte VARCHAR(100),
    descriptifCarte TEXT,
    couleurCarte VARCHAR(10),
    idColonne INT NOT NULL,
    PRIMARY KEY (idCarte),
    FOREIGN KEY (idColonne) REFERENCES Colonnes
);

CREATE TABLE Affecte(
    idCarte INT NOT NULL,
    login VARCHAR(100) NOT NULL,
    PRIMARY KEY (idCarte, login),
    FOREIGN KEY (idCarte) REFERENCES Cartes,
    FOREIGN KEY (login) REFERENCES Utilisateurs
)