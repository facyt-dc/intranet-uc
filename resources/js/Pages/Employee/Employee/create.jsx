import AdminLayout from "@/Layouts/AdminLayout";
import { Head } from "@inertiajs/react";
import React from "react";
import {useState} from "react"
import { useForm } from '@inertiajs/react'

import { Link } from "@inertiajs/react";

import ArrowCircleLeftRoundedIcon from "@mui/icons-material/ArrowCircleLeftRounded";
import IconButton from "@mui/material/IconButton";
import Tooltip from "@mui/material/Tooltip";
import { useTranslation } from "react-i18next";
import Box from '@mui/material/Box';
import TextField from '@mui/material/TextField';
import FormGroup from '@mui/material/FormGroup';
import FormControl from '@mui/material/FormControl';
import InputLabel from "@mui/material/InputLabel";
import Select from '@mui/material/Select';
import MenuItem from '@mui/material/MenuItem';
import Button from "@mui/material/Button";
import Alert from "@/Components/Alert";
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import dayjs from 'dayjs'
import TextareaAutosize from '@mui/material/TextareaAutosize';

export default function EmployeeCreate({ auth, staffs }) {
    const { t } = useTranslation(["common"]);
    const [showAlert, setShowAlert] = useState(false)
    const { data, setData, patch, post, processing, errors } = useForm({
        name:"",
        staff:1,
        teaching_level:0,
        address:"",
        phone: "",
        cedula: "",
        lastname: "",
        email: "",
        birthday: dayjs('2000-01-01')
    })
    
    const handleBirthDay = (newDate) => {
       setData(prevData => ({
        ...prevData,
        birthday: newDate
       }))
    }

    const handleChange = (e) => {
        const key   = e.target.id;
        const value = isNaN(e.target.value) ? e.target.value.trimStart() : Number(e.target.value);

        setData(prevData => ({
            ...prevData,
            [key]: value,
        }))
    }

    const handleSelectChange = (e) => {
        const key   = e.target.name;
        const value = Number(e.target.value);

        if(key == 'staff'){
            setData(prevData => ({
                ...prevData,
                [key]: value,
                ["teaching_level"]: 0
            }))
        }
        else {
            setData(prevData => ({
                ...prevData,
                [key]: value,
            }))
        }
        
    }

    const handleSubmit = (e) => {
        e.preventDefault();
        const validateCedula = /^[VJ]{1}\d{7,9}$/

        console.log(data)
        
        if(!validateCedula.test(data.cedula)){
            setShowAlert(true)
            setInterval(() => setShowAlert(false), 5000)

            setData(prevData => {
                return {
                    ...prevData,
                    cedula:""
                }
            })
            return
        }
        
        post(route("employee.store"));
        // else patch(route(routeName,dataFormObject.id));

    };


    return (
        <AdminLayout auth={auth}>
            {
                showAlert && (
                    <Alert
                        key={1}
                        message={"Cédula incorrecta"}
                        severity={"error"}
                    />
                )
            }

            <Head
                title={t("button.create field", {
                    field: "Empleado",
                })}
            />

            <div className="flex justify-between items-center">
                <h2 className="text-xl text-gray-500">
                    {t("button.create field", {
                        field: "Empleado",
                    })}
                </h2>
                <Link href={route("employee.index")}>
                    <Tooltip title={t("button.go back")}>
                        <IconButton size="large">
                            <ArrowCircleLeftRoundedIcon fontSize="inherit" />
                        </IconButton>
                    </Tooltip>
                </Link>
            </div>

            <Box
                component="form"
                sx={{ mt:2 }}
                onSubmit={handleSubmit}
            >

                <FormGroup sx={{ mt:2 }} >
                    <TextField
                        required
                        variant="outlined"
                        type={"text"}
                        value={data.name}
                        id={"name"}
                        name={"name"}
                        label={"Nombres"}
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    />

                    <TextField
                        required
                        variant="outlined"
                        type={"text"}
                        id={"lastname"}
                        name={"lastname"}
                        label={"Apellidos"}
                        value={data.lastname}
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    />

                    <LocalizationProvider dateAdapter={AdapterDayjs}>
                        <DatePicker
                            label="Fecha de nacimiento"
                            value={data.birthday}
                            id="birthday"
                            name="birthday"
                            format="D/M/YYYY"
                            onChange={(newDate) => handleBirthDay(newDate)}
                            sx={{m:1, maxWidth: 300 }}
                            required
                        />
                    </LocalizationProvider>

                    <TextField
                        required
                        variant="outlined"
                        type={"text"}
                        id={"cedula"}
                        name={"cedula"}
                        label={"Cédula"}
                        value={data.cedula}
                        placeholder="Ingrese una letra (V o J) seguido de hasta 9 dígitos"
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    />

                    {/* <TextField
                        required
                        variant="outlined"
                        id={"address"}
                        name={"address"}
                        label={"Dirección"}
                        value={data.address}
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    /> */}

                    <TextareaAutosize
                        id={"address"}
                        name={"address"}
                        label={"Dirección"}
                        minRows={1}
                        value={data.address}
                        onChange={handleChange}
                        placeholder="Dirección de residencia"
                        style={{width: 300, margin: 9 }}
                    />

                    <TextField
                        required
                        variant="outlined"
                        type={"tel"}
                        id={"phone"}
                        name={"phone"}
                        label={"Teléfono"}
                        defaultValue={""}
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    />

                    <TextField
                        required
                        variant="outlined"
                        type={"email"}
                        id={"email"}
                        name={"email"}
                        label={"Correo electrónico"}
                        defaultValue={""}
                        onChange={handleChange}
                        sx={{m:1, maxWidth: 300 }}
                        error={errors.name}
                        helperText={errors.name}
                    />

                    <FormControl sx={{mt:2}} >
                        <InputLabel id={`staff-label`}>{'Cargo'}</InputLabel>
                        <Select
                            value={data.staff}
                            onChange={handleSelectChange}
                            labelId={`staff-label`}
                            id={'staff'}
                            name={'staff'}
                            sx={{m:1, maxWidth: 300 }} 
                        >
                            {
                                staffs.map( (sv,_index) => (
                                    <MenuItem 
                                        key={_index}
                                        value={sv.id}
                                    >
                                        {sv.name}
                                    </MenuItem>
                                ) )
                            }
                        </Select>
                    </FormControl>

                    {
                        staffs.find(s => s.id == data.staff && s.type.name == 'Docente') && (
                            <FormControl sx={{mt:1}} >
                                <InputLabel id={`teaching-level-label`}>{'Nivel de docencia'}</InputLabel>
                                <Select
                                    value={data.teaching_level}
                                    onChange={handleSelectChange}
                                    labelId={`teaching-level-label`}
                                    id={'teaching_level'}
                                    name={'teaching_level'}
                                    sx={{m:1, maxWidth: 300 }} 
                                >
                                    {
                                        [{id:0,name:'Ninguno'},...staffs.find(s => s.id == data.staff && s.type.name == 'Docente').teaching_levels].map( (sv,_index) => (
                                            <MenuItem 
                                                key={_index}
                                                value={sv.id}
                                            >
                                                {sv.name}
                                            </MenuItem>
                                        ) )
                                    }
                                </Select>
                            </FormControl>
                        )
                    }
                </FormGroup>

                <div className="m-3">
                    <p className="text-xl">Beneficios</p>
                    <ul className="list-disc ml-10">
                        {
                            staffs.find(s => s.id == data.staff).benefits.map((b, _index) => (
                                <li key={_index}>{b.name}</li>
                            ))
                        }
                    </ul>
                </div>

                <Button 
                    variant="contained" 
                    type="submit" 
                    disabled={processing} 
                    sx={{mt:2}} 
                >
                    {'Crear'}
                </Button>
            </Box>
        </AdminLayout>
    );
}