import React from 'react';

const CountryFlag = (props) => {
    const { flagCode, url, name } = props;
    const svgPath = `${url}assets/flags/${flagCode}.svg`;
    return <div className="lsdp-lang-image"><img src={svgPath} alt={name} /></div>
};

const CountryName = (props) => {
    return <div className="lsdp-lang-name">{props.name}</div>
}

const CountryCode = (props) => {
    return <div className="lsdp-lang-code">{props.code}</div>
}

export { CountryFlag, CountryName, CountryCode };
