<?php
include_once('../connect_db.php');
$conn = connect_to_db();

$query = "CREATE TABLE IF NOT EXISTS Saliva(
    id           INT(11)     NOT NULL     AUTO_INCREMENT  PRIMARY KEY,
    CreateDate   DATE        NOT NULL,
    Date	 DATE        NOT NULL,
    Time  	 TIME        NOT NULL,
    Timestamp    BIGINT(20)  NOT NULL,
    Week	 INT(11)     NOT NULL,
    CassetteId   VARCHAR(64) NOT NULL,
    IsUsed     TINYINT(1)  NOT NULL,
    UserId     VARCHAR(64)

)";

$query2 = "CREATE TABLE IF NOT EXISTS Patient(
    id                   INT(11)     NOT NULL   AUTO_INCREMENT  PRIMARY KEY,
    UserId               VARCHAR(64) NOT NULL,
    JoinDate             DATE        NOT NULL,
    DeviceId             VARCHAR(64) NOT NULL,
    IsDropout            TINYINT(1)  NOT NULL   DEFAULT 0,
    DropoutDate          DATE        DEFAULT NULL,
    UsedScore            INT(11)     NOT NULL,
    ConnectionCheckTime DATETIME    DEFAULT NULL,
    Week	         INT(11)     NOT NULL,
    Position             INT(4)     DEFAULT NULL,
    AppVersion           VARCHAR(12) DEFAULT NULL

)";

$query3 = "CREATE TABLE IF NOT EXISTS TestResult(
    id          INT(11)     NOT NULL    AUTO_INCREMENT PRIMARY KEY,
    UserId      VARCHAR(64) NOT NULL,
    Result      INT(2)      NOT NULL,
    DeviceId    VARCHAR(64) NOT NULL,
    CassetteId  VARCHAR(64) NOT NULL,
    Date        DATE        NOT NULL,
    Time        TIME        NOT NULL,
    Timestamp   BIGINT(20)  NOT NULL,
    IsPrime     TINYINT(1)  NOT NULL,
    IsFilled    TINYINT(1)  NOT NULL,
    TimeSlot    INT(11)     NOT NULL,
    Score       INT(11)     NOT NULL
)";

$query4 = "CREATE TABLE IF NOT EXISTS NoteAdd(
    id              INT(11)      NOT NULL     AUTO_INCREMENT   PRIMARY KEY,
    UserId          VARCHAR(64)  NOT NULL,
    IsAfterTest     TINYINT(1)   NOT NULL,
    Date            DATE         NOT NULL,
    Time            TIME     NOT NULL,
    Timestamp       BIGINT(20)   NOT NULL,
    Week            INT(11)      NOT NULL,
    RecordDate      DATE         NOT NULL,
    RecordTimeslot  INT(2)       NOT NULL,
    Category        TINYINT(1)   NOT NULL,
    Type            INT(2)       NOT NULL,
    Items           INT(2)       NOT NULL,
    Impact          INT(2)       NOT NULL,
    Description     VARCHAR(255)         ,
    Score           INT(11)      NOT NULL
)";

$query5 = "CREATE TABLE IF NOT EXISTS TestDetail(
    id                  INT(11)     NOT NULL    AUTO_INCREMENT   PRIMARY KEY,
    UserId              VARCHAR(64) NOT NULL,
    Date                DATE        NOT NULL,
    Time                TIME    NOT NULL,
    Timestamp           BIGINT(20)  NOT NULL,
    Week		INT(11)	    NOT NULL,
    DeviceId            VARCHAR(64) NOT NULL,
    CassetteId          VARCHAR(64),
    FailedState         INT(2),
    FirstVoltage        INT(8),
    SecondVoltage       INT(8),
    DevicePower         INT(8),
    ColorReading        INT(8),
    ConnectionFailRate  INT(8),
    FailedReason        VARCHAR(64),
    HardwareVersion     VARCHAR(12) NOT NULL,
    AppVersion		VARCHAR(12) NOT NULL

)";

$query6 = "CREATE TABLE IF NOT EXISTS QuestionTest(
    id                  INT(11)     NOT NULL    AUTO_INCREMENT PRIMARY KEY,
    UserId              VARCHAR(64) NOT NULL,
    Date                DATE        NOT NULL,
    Time                TIME        NOT NULL,
    Timeslot            INT(2)      NOT NULL,
    Timestamp           BIGINT(20)  NOT NULL,
    Week	        INT(11)     NOT NULL,
    Type                INT(4)      NOT NULL,
    isCorrect           TINYINT(1)  NOT NULL,
    Selection           VARCHAR(64) NOT NULL,
    Choose              INT(2)      NOT NULL,
    Score               INT(11)     NOT NULL
)";


$query7 = "CREATE TABLE IF NOT EXISTS CopingSkill(
        id                  INT(11)     NOT NULL    AUTO_INCREMENT PRIMARY KEY,
        UserId              VARCHAR(64) NOT NULL,
        Date                DATE        NOT NULL,
        Time                TIME        NOT NULL,
        Timeslot            INT(2)      NOT NULL,
        Timestamp           BIGINT(20)  NOT NULL,
        Week                INT(11)	NOT NULL,
        SkillType           INT(4)      NOT NULL,
        SkillSelect         INT(4)      NOT NULL,
        Recreation          VARCHAR(64) ,
        Score               INT(11)     NOT NULL
)";

$query8 = "CREATE TABLE IF NOT EXISTS ExchangeHistory(
        id                  INT(11)     NOT NULL    AUTO_INCREMENT PRIMARY KEY,
        UserId              VARCHAR(64) NOT NULL,
        Date                DATE        NOT NULL,
        Time                TIME        NOT NULL,
        Timestamp           BIGINT(20)  NOT NULL,
	Week	            INT(11)	NOT NULL,
        NumOfCounter        INT(11)     NOT NULL
)";

$query9 = "CREATE TABLE IF NOT EXISTS Appeal(
        id                  INT(11)     NOT NULL    AUTO_INCREMENT PRIMARY KEY,
        UserId              VARCHAR(64) NOT NULL,
        Timestamp           BIGINT(20)  NOT NULL,
        AppealType          INT(11)     NOT NULL,
        AppealTimes         INT(11)     NOT NULL
)";



#$result = mysql_query($sql);

if ( mysql_query($query, $conn) && mysql_query($query2, $conn) 
    && mysql_query($query3, $conn) && mysql_query($query4, $conn) 
    && mysql_query($query5, $conn) && mysql_query($query6, $conn)
    && mysql_query($query7, $conn) && mysql_query($query8, $conn)) {
        echo "All Table created successfully\n";
} else {
        echo "Error creating table: " . mysql_error($conn)."\n";
}

mysql_close($conn);
?>
