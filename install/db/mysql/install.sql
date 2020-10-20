create table if not exists qsoft_payme_transaction
(
	ID int not null auto_increment,
	ORDER_ID varchar(255) NOT NULL,
	TID varchar(255),
	AMOUNT varchar(255),
	STATE varchar(255),
	CREATE_TIME varchar(255),
	PERFORM_TIME varchar(255),
	CANCEL_TIME varchar(255),
	PAYCOM_TIME varchar(255),
	PAYCOM_DATETIME varchar(255),
	REASON varchar(255),
	primary key (ID)
);