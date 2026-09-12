// External Dependencies
import React, { Component } from 'react';

// Internal Dependencies
import './style.css';

class lsdp_field extends Component {

  static slug = 'lsdp_field';

  /**
   * Handle input value change.
   *
   * @param {object} event
   */
  _onChange = (event) => {
    this.props._onChange(this.props.name, event.target.value);
  }

  render() {
    return(
      <input
        id={`lsdp-field-${this.props.name}`}
        name={this.props.name}
        value={this.props.value}
        type='text'
        className='lsdp-input'
        onChange={this._onChange}
        placeholder='Your text here ...'
      />
    );
  }
}

export default lsdp_field;
