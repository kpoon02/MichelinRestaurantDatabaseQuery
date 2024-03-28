CREATE TABLE Location (
    Address VARCHAR,
    PostalCode VARCHAR,
    City VARCHAR NOT NULL,
    Province VARCHAR NOT NULL,
    PRIMARY KEY (Address, PostalCode)
);

CREATE TABLE Restaurant (
    RestaurantID INT,
    Name VARCHAR NOT NULL,
    Website VARCHAR NOT NULL,
    PriceRange VARCHAR,
    Address VARCHAR UNIQUE,
    PostalCode VARCHAR UNIQUE,
    AverageScore DECIMAL,
    CuisideID INT UNIQUE,
    PRIMARY KEY (RestarutantID),
    FOREIGN KEY (Address) REFERENCES Location(Address) ON UPDATE CASCADE ON DELETE SET NULL,
    FOREIGN KEY (Address) REFERENCES Location(PostalCode) ON UPDATE CASCADE ON DELETE SET NULL, --are we still doing one to many?
    FOREIGN KEY (CuisineID) REFERENCES Cuisine
);

CREATE TABLE Cuisine (
    CuisineID INT,
    Name VARCHAR NOT NULL,
    Description VARCHAR,
    PRIMARY KEY (CuisineID) --are we modifying this to be many to one?
);

CREATE TABLE RestaurantServes (
    RestaurantID INT NOT NULL,
    CuisineID INT NOT NULL,
    PRIMARY KEY (RestaurantID, CuisineID),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant(RestaurantID),
    FOREIGN KEY (CuisineID) REFERENCES Cuisine(CuisineID)
);

CREATE TABLE LeadChef (
    ChefID INT,
    Name VARCHAR NOT NULL,
    Biography TEXT,
    PRIMARY KEY (ChefID)
);

CREATE TABLE WorksAtRestaurant (
    ChefID INT NOT NULL,
    RestaurantID INT NOT NULL,
    Since DATE,
    Until DATE OR CHAR(7), --'present'
    PRIMARY KEY (RestaurantID),
    FOREIGN KEY (ChefID) REFERENCES LeadChef(ChefID),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant(RestaurantID)
);

CREATE TABLE SignatureDish (
    RestaurantID INT NOT NULL,
    DishName VARCHAR NOT NULL,
    Description VARCHAR,
    Course VARCHAR,
    PRIMARY KEY (RestarutantID, DishName),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant (RestarutantID)
);

CREATE TABLE KnownFor (
    DishName VARCHAR NOT NULL,
    RestaurantID INT NOT NULL,
    PRIMARY KEY (RestaurantID, DishName)
    FOREIGN KEY (DishName) REFERENCES SignatureDish(DishName),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant(RestaurantID)
);

CREATE TABLE Award (
    AwardID INT,
    Name VARCHAR NOT NULL,
    MichelinRating INT,
    Description TEXT,
    RestaurantID INT,
    PRIMARY KEY (AwardID),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant(RestaurantID) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE Reviewer (
    ReviewerID INT,
    Name VARCHAR NOT NULL,
    PRIMARY KEY (ReviewerID)
);

CREATE TABLE ProfessionalCritic (
    ReviewerID INT NOT NULL,
    Title VARCHAR, 
    PRIMARY KEY (ReviewerID),
    FOREIGN KEY (ReviewerID) REFERENCES (Reviewer) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE FoodBlogger (
    ReviewerID INT NOT NULL,
    Website VARCHAR, -- candidate/composite key/unique constraint? Food critic missing something
    PRIMARY KEY (ReviewerID),
    FOREIGN KEY (ReviewerID) REFERENCES (Reviewer) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE Review (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    Date DATE NOT NULL,
    Comment TEXT,
    Score DECIMAL,
    FOREIGN KEY (HighlightDish, RestaurantID) REFERENCES SignatureDish(DishName, RestaurantID) ON UPDATE CASCADE ON DELETE SET NULL
);

CREATE TABLE RestaurantHasReview (
    ReviewID INT NOT NULL,
    RestaurantID INT NOT NULL,
    PRIMARY KEY (ReviewID),
    FOREIGN KEY (ReviewID) REFERENCES Review(ReviewID),
    FOREIGN KEY (RestaurantID) REFERENCES Restaurant(RestaurantID)
);

CREATE TABLE ReviewerHasReview (
    ReviewID INT NOT NULL,
    ReviewerID INT NOT NULL,
    PRIMARY KEY (ReviewID),
    FOREIGN KEY (ReviewID) REFERENCES Review(ReviewID),
    FOREIGN KEY (ReviewerID) REFERENCES Reviewer(ReviewerID)
);

