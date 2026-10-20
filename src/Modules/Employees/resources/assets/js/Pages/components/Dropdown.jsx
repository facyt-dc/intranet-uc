import Dropdown from "@/Components/Dropdown";
import Button from "@mui/material/Button";
import MenuRoundedIcon from "@mui/icons-material/MenuRounded";
import KeyboardArrowDownRoundedIcon from "@mui/icons-material/KeyboardArrowDownRounded";

export default function EmployeeDropdownMenu({ auth, links }) {
    const isAdmin = auth.permissions.find(
        (permission) => permission.name === "isAdmin"
    );

    const dropDownContentStyle = {
        fontSize: '20px'
    }

    return (
            <Dropdown>
                <Dropdown.Trigger>
                    <Button
                        variant="outlined"
                        startIcon={<MenuRoundedIcon />}
                        endIcon={<KeyboardArrowDownRoundedIcon />}
                        aria-haspopup="menu"
                    >
                        Secciones
                    </Button>
                </Dropdown.Trigger>

                <Dropdown.Content align="left" style={dropDownContentStyle}>
                    {
                        links.map( (_link,index) => (
                            <Dropdown.Link
                                href={route(`${_link.route}`)}
                                key={index}
                            >
                                {`${_link.title}`}
                            </Dropdown.Link>
                        ) )
                    }
                </Dropdown.Content>
            </Dropdown>
    );
}
