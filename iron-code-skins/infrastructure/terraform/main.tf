resource "aws_vpc" "iron_code_vpc" {
  cidr_block = "10.0.0.0/16"
  enable_dns_support = true
  enable_dns_hostnames = true
  tags = {
    Name = "iron_code_vpc"
  }
}

resource "aws_subnet" "iron_code_subnet" {
  vpc_id            = aws_vpc.iron_code_vpc.id
  cidr_block        = "10.0.1.0/24"
  availability_zone = "us-east-1a"
  tags = {
    Name = "iron_code_subnet"
  }
}

resource "aws_internet_gateway" "iron_code_ig" {
  vpc_id = aws_vpc.iron_code_vpc.id
  tags = {
    Name = "iron_code_ig"
  }
}

resource "aws_route_table" "iron_code_route_table" {
  vpc_id = aws_vpc.iron_code_vpc.id
  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = aws_internet_gateway.iron_code_ig.id
  }
  tags = {
    Name = "iron_code_route_table"
  }
}

resource "aws_route_table_association" "iron_code_route_table_association" {
  subnet_id      = aws_subnet.iron_code_subnet.id
  route_table_id = aws_route_table.iron_code_route_table.id
}

resource "aws_security_group" "iron_code_sg" {
  vpc_id = aws_vpc.iron_code_vpc.id
  name   = "iron_code_sg"
  ingress {
    from_port   = 80
    to_port     = 80
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  ingress {
    from_port   = 443
    to_port     = 443
    protocol    = "tcp"
    cidr_blocks = ["0.0.0.0/0"]
  }
  egress {
    from_port   = 0
    to_port     = 0
    protocol    = "-1"
    cidr_blocks = ["0.0.0.0/0"]
  }
  tags = {
    Name = "iron_code_sg"
  }
}

resource "aws_rds_instance" "iron_code_db" {
  identifier              = "iron-code-db"
  engine                 = "mysql"
  engine_version         = "8.0"
  instance_class         = "db.t2.micro"
  allocated_storage       = 20
  storage_type           = "gp2"
  username               = "admin"
  password               = "password"
  db_name                = "iron_code_db"
  skip_final_snapshot    = true
  vpc_security_group_ids = [aws_security_group.iron_code_sg.id]
  db_subnet_group_name   = aws_db_subnet_group.iron_code_db_subnet_group.name
}

resource "aws_db_subnet_group" "iron_code_db_subnet_group" {
  name       = "iron_code_db_subnet_group"
  subnet_ids = [aws_subnet.iron_code_subnet.id]
  tags = {
    Name = "iron_code_db_subnet_group"
  }
}