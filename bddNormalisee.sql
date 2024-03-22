CREATE TABLE Utilisateurs
(
    login    VARCHAR(100) NOT NULL,
    nom      VARCHAR(100) NOT NULL,
    prenom   VARCHAR(100) NOT NULL,
    email    VARCHAR(100) NOT NULL,
    mdpHache VARCHAR(100) NOT NULL,
    PRIMARY KEY (login)
);

CREATE TABLE Tableaux(
    idTableau INT NOT NULL,
    login VARCHAR(100) NOT NULL,
    codeTableau VARCHAR(255),

)