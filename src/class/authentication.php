<?php

namespace AuthenticationNSC;

use ArrayObject;
use DateTime;
use Error;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Helper\helper;
use InterfaceListNSC\_Authentication;
use InterfaceListNSC\_Fabric;
use InterfaceListNSC\_Product;
use InterfaceListNSC\_Schema;
use InterfaceListNSC\_SingletonInitializer;
use InterfaceListNSC\_Token;
use ProductsNSC\AuthenticationUser;
use SchemaBuilder;
use UserSchema;

class Authentication implements _SingletonInitializer, _Authentication, _Fabric
{
    private static ?Authentication $instance = null;
    private _Schema $_currentUser;

    private ?ArrayObject $_authenticationData = null;
    private bool  $_authenticated = false;

    private _Token $_tokenData;




    public function __construct()
    {

        $this->_tokenData = new Token();
        $this->_Initialize((object)[]);

    }


    public static function getInstance(): authentication
    {
        return static::$instance ?: static::$instance = new static();
    }

    function _Initialize(object $args)
    {
        $this->_CheckAuthenticate();
        $this->changeAuthenticated();
        $this->_currentUser = $this->_CreateProduct()->_GetSchema();
    }

    function _GetCurrentUser(): _Schema
    {
        return $this->_currentUser;
    }

    function _SetCurrentUser(_Schema $schema)
    {
        $this->_currentUser = $schema;
    }

    function _IsAuthenticated(): bool
    {
        return $this->_authenticated;
    }

    function _CreateToken(_Schema $schema): string
    {

        $this->_tokenData->setValueInside($schema);
        return JWT::encode($this->_tokenData->_GetData(), $this->_tokenData->_GetKey());
    }


    function _CheckAuthenticate()
    {

        if (filter_has_var(INPUT_SERVER, "HTTP_AUTHENTICATE")) {

            try {
                $data = JWT::decode(apache_request_headers()["AUTHENTICATE"], $this->_tokenData->_GetKey(), array(Token::AUTHENTICATION_ALG));
                $this->_authenticationData = new ArrayObject($data);
            }
            catch (SignatureInvalidException $e) {}
            catch (ExpiredException $e){}
        }

    }

    private function changeAuthenticated()
    {
        if (!is_null($this->_authenticationData)) {
            $this->_authenticated = ($this->_authenticationData->offsetGet('exp') >= time());
        }
    }
    function _CreateProduct(): _Product
    {
        /**@var  _Product $currentProduct */
        if ($this->_authenticated) {
            $currentProduct = new AuthenticationUser(new UserSchema($this->_authenticationData->offsetGet(Token::INSIDE_VALUE)));
        } else {

            $currentProduct = GuestCreating::createGuest((object)[]);
        }
        return $currentProduct;
    }
}


class GuestCreating
{
    public const GUESTDEF = 'unknown';

    public static function createGuest(object $args): AuthenticationUser
    {
        return new AuthenticationUser(new UserSchema([
            UserSchema::USERNAME => $args->{UserSchema::USERNAME} ?? self::GUESTDEF,
            UserSchema::PASSWORD => $args->{UserSchema::USERNAME} ?? self::GUESTDEF,
            UserSchema::EMAIL => $args->{UserSchema::USERNAME} ?? self::GUESTDEF,
            UserSchema::REGISTEREDAT => (new DateTime())->format(SchemaBuilder::DATEFORMAT),
            UserSchema::LASTLOGIN => (new DateTime())->format(SchemaBuilder::DATEFORMAT)
        ]));
    }
}


class Token implements _Token
{


    public const AUTHENTICATION_ALG = "HS256";
    private object $_tokenData;
    private string $_key;


    private const FILENAME = 'jwt';

    public const KEY = 'key';
    public const EXPERIENCE = 'exp';
    public const ISSUER = 'iss';
    public const AUDIENCE = 'aud';
    public const IAT = 'iat';
    public const NBF = 'nbf';

    public const INSIDE_VALUE = 'data';


    public function __construct()
    {
        if (stream_resolve_include_path(self::FILENAME . '.ini')) {
            try{
                $configs = Helper::getIniConfiguration(self::FILENAME);
                $this->_key = $configs[self::KEY];
                $this->_tokenData = (object)[
                    //issue claimer
                    self::ISSUER => $configs[self::ISSUER],
                    //getter issues
                    self::AUDIENCE => $configs[self::AUDIENCE],
                    //not before claim
                    self::NBF => time(),
                    self::IAT => time(),
                    self::EXPERIENCE => time() + $configs[self::EXPERIENCE]];
            }catch (Error $e){
                throw new Error("Need all properties: key,exp,iss,aud,iat,nbf");
            }

        } else
            throw new Error("Configuration file must be included inside project, create 'jwt.ini' file inside configuration folder");
    }

    function _GetKey()
    {
        return $this->_key;
    }

    function _GetData(): object
    {
        return $this->_tokenData;
    }

    function _GetExper(): int
    {
        return $this->_tokenData->{self::IAT} + $this->_tokenData->{self::EXPERIENCE};
    }


    function setValueInside(_Schema $value)
    {
        $this->_tokenData->{self::INSIDE_VALUE} = $value->_GetAllItems();
    }

}
