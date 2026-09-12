// External Dependencies
import React, { Component } from 'react';
import { CountryFlag, CountryName, CountryCode, staticCSS } from '../render_content';

// Internal Dependencies
import './style.css';

class LSDP_Module extends Component {

  static slug = 'language-switcher-for-divi-polylang';

  constructor(props) {
    super(props);
    this.state = {
      languageData: null,
      currentLang: null
    };
  }

  componentDidMount() {
    setTimeout(() => {
      const polylangData =window.ETBuilderBackendDynamic &&  window.ETBuilderBackendDynamic.lsdpGlobalObj &&  window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpLanguageData ? window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpLanguageData : [];
      const currentLang = window.ETBuilderBackendDynamic && window.ETBuilderBackendDynamic.lsdpGlobalObj &&  window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpCurrentLang ? window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpCurrentLang : '';
      this.setState({ polylangData, currentLang });
      const thisModule = document.querySelector('.lsdp-wrapper.dropdown');
      const parentRow = thisModule.closest('.et_pb_row');
      if (parentRow) {
        parentRow.style.setProperty('z-index', '999');
      }
    }, 1000);
  }


  static css(props) {
    return staticCSS(props);
  }


  render() {
    const { polylangData, currentLang } = this.state;
    const style = this.props.lsdp_style ? this.props.lsdp_style : 'dropdown';
    const flagDisplay = this.props.lsdp_flag_visibility ? this.props.lsdp_flag_visibility : 'on';
    const nameDisplay = this.props.lsdp_language_name_visibility ? this.props.lsdp_language_name_visibility : 'on';
    const codeDisplay = this.props.lsdp_language_code_visibility ? this.props.lsdp_language_code_visibility : 'off';
    const hideCurrentLang = this.props.lsdp_current_lang_visibility ? this.props.lsdp_current_lang_visibility : 'off';
    const hideUntranslateLang = this.props.lsdp_unstranslated_lang_visibility ? this.props.lsdp_unstranslated_lang_visibility : 'off';
    const pluginUrl=window.ETBuilderBackendDynamic && window.ETBuilderBackendDynamic.lsdpGlobalObj && window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpPluginUrl ? window.ETBuilderBackendDynamic.lsdpGlobalObj.lsdpPluginUrl : '';
 
    return (
      polylangData && Object.keys(polylangData) && Object.keys(polylangData).length > 0? (
        <>
        <div className={`lsdp-wrapper ${style}`}>
        {'dropdown' === style &&
          (
            polylangData && polylangData[currentLang] ? (
            <span>
              <a href={polylangData[currentLang].url}>
                {flagDisplay === 'on' && <CountryFlag flagCode={polylangData[currentLang].flagCode} name={polylangData[currentLang].name} url={pluginUrl}/>}
                {nameDisplay === 'on' && <CountryName name={polylangData[currentLang].name} />}
                {codeDisplay === 'on' && <CountryCode code={polylangData[currentLang].slug} />}
              </a>
            </span>
          ):<>No current available languages</>)}
        {(
          <>
          <ul className="lsdp-language-list">
            {
              polylangData && Object.keys(polylangData) && Object.keys(polylangData).length > 0 ? (
                <>
                  {Object.keys(polylangData).map((lang, index) => {
                    if (lang === currentLang && 'on' === hideCurrentLang) {
                      return null;
                    }

                    if (polylangData[lang].no_translation && 'on' === hideUntranslateLang) {
                      return null;
                    }

                    if (lang === currentLang && 'dropdown' === style) {
                      return null;
                    }

                    return (
                      <li key={index} className={lang === currentLang ? 'lsdp_active_lang' : ''}>
                        <a href={polylangData[lang].url}>
                          {flagDisplay === 'on' && <CountryFlag flagCode={polylangData[lang].flagCode} name={polylangData[lang].name} url={pluginUrl}/>}
                          {nameDisplay === 'on' && <CountryName name={polylangData[lang].name} />}
                          {codeDisplay === 'on' && <CountryCode code={polylangData[lang].slug} />}
                        </a>
                      </li>
                    );
                  })}
                </>
              ):(<>No available languages</>)
            }
          </ul>
          </>
        )}
      </div></>
      ):<>No available languages</>
  
    );
  }
}

export default LSDP_Module;
